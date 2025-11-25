<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class S3FileManager {
    protected $file; // Uploaded file or local path
    public $path; // Folder path in bucket
    public $filename; // Filename to store
    public $thumb; // Thumbnail size (e.g., "100x100")
    public $size; // Main image resize size (e.g., "800x600")
    protected $isImage; // Is the file an image
    protected $config; // Storage config
    protected $extension; // File extension
    protected $isVerify; // verify storage
    protected $storage; // storage model instance
    protected $uploaded = false; // track upload success
    protected $httpClient; // persistent HTTP client

    public function __construct($storage, $file = null, $isVerify = false) {
        $this->file     = $file;
        $this->storage  = $storage;
        $this->config   = $this->storage->config ?? NULL;
        $this->isVerify = $isVerify;

        if ($file && is_object($file)) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $this->isImage   = in_array(strtolower($file->getClientOriginalExtension()), $imageExtensions);
        }
    }

    /**
     * Create or reuse persistent HTTP client
     */
    protected function getHttpClient() {
        if (!$this->httpClient) {
            $this->httpClient = Http::withOptions([
                'headers'         => [
                    'Connection' => 'keep-alive', // enable connection reuse
                ],
                'verify'          => false, // disable SSL verify in bulk/non-verify mode
                'timeout'         => 60,
                'connect_timeout' => 10,
            ]);
        }
        return $this->httpClient;
    }

    public function upload() {
        if (!$this->path) {
            return false;
        }

        if (!$this->filename) {
            $this->filename = $this->generateFileName();
        }


        if ($this->isImage) {
            $this->uploaded = $this->uploadImage();
        } else {
            $this->uploaded = $this->uploadFile();
        }

        return $this->uploaded ? $this->url() : false;
    }

    protected function uploadImage() {
        try {
            $manager = new ImageManager(new Driver());
            $image   = $manager->read($this->file);

            // Resize main image if size provided
            if ($this->size) {
                $size = explode('x', strtolower($this->size));
                $image->resize($size[0], $size[1]);
            }

            // Upload main image
            if (!$this->httpPut($this->path . '/' . $this->filename, (string) $image->encode())) {
                return false;
            }

            // Optional thumbnail
            if ($this->thumb) {
                $thumbSize = explode('x', $this->thumb);
                $thumb     = $manager->read($this->file)
                    ->resize($thumbSize[0], $thumbSize[1])
                    ->encode('jpg');

                if (!$this->httpPut($this->path . '/thumb_' . $this->filename, (string) $thumb)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            if ($this->isVerify) {
                $this->storage->error_message = $e->getMessage();
                $this->storage->save();
            }
            return false;
        }
    }

    protected function uploadFile() {
        try {
            $content = is_object($this->file)
            ? file_get_contents($this->file->getRealPath())
            : file_get_contents($this->file);

            return $this->httpPut($this->path . '/' . $this->filename, $content);
        } catch (\Throwable $e) {
            if ($this->isVerify) {
                $this->storage->error_message = $e->getMessage();
                $this->storage->save();
            }
            return false;
        }
    }


    protected function generateFileName() {
        $extension = is_object($this->file)
        ? $this->file->getClientOriginalExtension()
        : pathinfo($this->file, PATHINFO_EXTENSION);
        $this->extension = $extension;
        return uniqid() . '_' . time() . '.' . $extension;
    }

    public function url() {
        if (!$this->uploaded) {
            return false;
        }

        return $this->path . '/' . $this->filename;
    }

    /**
     * Perform signed HTTP PUT (with persistent client)
     */
    protected function httpPut($key, $content) {
        $url            = rtrim($this->config->end_point, '/') . '/' . trim($this->config->bucket_name, '/') . '/' . trim($key, '/');

        try {

            $signedHeaders = $this->signRequest('PUT', $key, $content, $this->config, ['x-amz-acl' => 'public-read']);

            $response = $this->getHttpClient()
                ->withHeaders($signedHeaders)
                ->withBody($content, $this->isImage ? ('image/' . $this->extension) : 'application/octet-stream')
                ->put($url);

            if (!$response->successful()) {
                if ($this->isVerify) {
                    $this->storage->error_message = "S3 upload failed: " . $response->body();
                    $this->storage->save();
                }

                return false;
            }

            return true;

        } catch (\Throwable $e) {

            if ($this->isVerify) {
                $this->storage->error_message = $e->getMessage();
                $this->storage->save();
            }

            return false;
        }
    }

    /**
     * Perform signed HTTP DELETE
     */
    public function httpDelete($key) {
        try {
            $url           = rtrim($this->config->end_point, '/') . '/' . $this->config->bucket_name . '/' . $key;
            $signedHeaders = $this->signRequest('DELETE', $key, '', $this->config);

            $response = $this->getHttpClient()
                ->withHeaders($signedHeaders)
                ->delete($url);

            if (!$response->successful()) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * AWS Signature V4 signing
     */
    protected function signRequest($method, $key, $body, $config, $extraHeaders = []) {
        $accessKey = $config->access_key;
        $secretKey = $config->secret_key;
        $region    = $config->region;
        $service   = 's3';

        $host      = parse_url($config->end_point, PHP_URL_HOST);
        $uri       = '/' . $config->bucket_name . '/' . $key;
        $t         = time();
        $amzDate   = gmdate('Ymd\THis\Z', $t);
        $dateStamp = gmdate('Ymd', $t);

        $payloadHash = hash('sha256', $body);

        $headers = array_merge([
            'host'                 => $host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date'           => $amzDate,
        ], $extraHeaders);

        ksort($headers);
        $canonicalHeaders = '';
        $signedHeaderKeys = [];
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= strtolower($k) . ':' . trim($v) . "\n";
            $signedHeaderKeys[] = strtolower($k);
        }
        $signedHeaders = implode(';', $signedHeaderKeys);

        $canonicalRequest = "$method\n$uri\n\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

        $algorithm       = 'AWS4-HMAC-SHA256';
        $credentialScope = "$dateStamp/$region/$service/aws4_request";
        $stringToSign    = "$algorithm\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        $kSecret   = 'AWS4' . $secretKey;
        $kDate     = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion   = hash_hmac('sha256', $region, $kDate, true);
        $kService  = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning  = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $headers['Authorization'] = "$algorithm Credential=$accessKey/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        return $headers;
    }

    public function getConfig() {
        return $this->config;
    }
}
