<?php

namespace App\Lib;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FTPFileManager {
    protected $file; // Uploaded file or local path
    public $path; // Folder path on FTP (relative inside root_folder)
    public $filename; // Filename to store
    public $thumb; // Thumbnail size (e.g., "100x100")
    public $size; // Main image resize size (e.g., "800x600")
    protected $isImage; // Is the file an image
    protected $config; // Storage config
    protected $extension; // File extension
    protected $isVerify; // verify storage
    protected $storage; // verify storage
    protected $uploaded = false; // track upload success

    // Static connection cache per host
    protected static $ftpConnections = []; // [host => connection]
    public function __construct($storage, $file = null, $isVerify = false) {
        $this->file     = $file;
        $this->storage  = $storage;
        $this->config   = $this->storage->config;
        $this->isVerify = $isVerify;

        if ($file && is_object($file)) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $this->isImage   = in_array(strtolower($file->getClientOriginalExtension()), $imageExtensions);
        }
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

            if ($this->size) {
                $size = explode('x', strtolower($this->size));
                $image->resize($size[0], $size[1]);
            }

            if (!$this->ftpPut($this->path . '/' . $this->filename, (string) $image->encode())) {
                throw new \Exception("Failed to upload main image [{$this->filename}]");
            }

            if ($this->thumb) {
                $thumbSize = explode('x', $this->thumb);
                $thumb     = $manager->read($this->file)
                    ->resize($thumbSize[0], $thumbSize[1])
                    ->encode('jpg');

                if (!$this->ftpPut($this->path . '/thumb_' . $this->filename, (string) $thumb)) {
                    throw new \Exception("Failed to upload thumbnail [thumb_{$this->filename}]");
                }
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function uploadFile() {
        try {
            $content = is_object($this->file)
            ? file_get_contents($this->file->getRealPath())
            : file_get_contents($this->file);

            return $this->ftpPut($this->path . '/' . $this->filename, $content);
        } catch (\Throwable $e) {
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
        return $this->uploaded ? trim($this->path, '/') . '/' . $this->filename : false;
    }

    protected function ftpConnect() {
        try {
            $host = $this->config->host;

            // reuse connection if exists
            if (!empty(self::$ftpConnections[$host])) {
                $conn = self::$ftpConnections[$host];
                if (@ftp_nlist($conn, '.')) {
                    return $conn;
                }
            }

            // Connect
            $conn = ftp_connect($host, $this->config->port);
            if (!$conn) {
                throw new \Exception("FTP connect failed: $host");
            }

            // Login
            if (!ftp_login($conn, $this->config->username, $this->config->password)) {
                throw new \Exception("FTP login failed: {$this->config->username}");
            }

            // Determine passive mode
            $passiveMode = $this->storage->config->passMode ?? null;

            if ($passiveMode !== null) {
                ftp_pasv($conn, $passiveMode);
            } else {
                $config                = $this->storage->config;

                ftp_pasv($conn, true);
                if (@ftp_nlist($conn, '.') === false) {
                    // True failed → try false
                    ftp_pasv($conn, false);
                    if (@ftp_nlist($conn, '.') === false) {
                        throw new \Exception("FTP passive mode test failed for host $host");
                    }

                    $config->passMode      = false;
                } else {
                    $config->passMode      = true;
                }

                $this->storage->config = $config;
                $this->storage->save();
            }

            // Save connection for reuse
            self::$ftpConnections[$host] = $conn;
            return $conn;

        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getRemotePath($file) {
        $root = trim($this->config->upload_folder, '/');
        return $root . '/' . ltrim($file, '/');
    }

    protected function ftpPut($remoteFile, $content, $attempts = 3) {
        $conn = $this->ftpConnect();
        if (!$conn) {
            return false;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'ftp');
        file_put_contents($tmpFile, $content);

        for ($i = 0; $i < $attempts; $i++) {

            try {
                $remoteFilePath = '/' . ltrim($this->getRemotePath($remoteFile), '/');
                $remoteDir      = dirname($remoteFilePath);

                $this->ftpEnsureDir($conn, $remoteDir);
                ftp_chdir($conn, '/');

                if (ftp_put($conn, $remoteFilePath, $tmpFile, FTP_BINARY)) {

                    unlink($tmpFile);
                    return true;
                }

            } catch (\Throwable $e) {
                usleep(500_000); // wait 0.5 sec before retry
            }
        }

        unlink($tmpFile);
        return false;
    }

    public function ftpDelete($filePath) {
        $conn = $this->ftpConnect();
        if (!$conn) {
            return false;
        }

        try {
            $remoteFile = $this->getRemotePath($filePath);
            $deleted    = ftp_delete($conn, $remoteFile);

            if (!$deleted) {
                throw new \Exception("FTP delete failed for file: $remoteFile");
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function ftpEnsureDir($conn, $dir) {
        try {
            $parts = explode('/', trim($dir, '/'));
            $path  = '';

            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }

                $path .= '/' . $part;
                if (!@ftp_chdir($conn, $path)) {
                    @ftp_mkdir($conn, $path);
                }
            }
        } catch (\Throwable $e) {
        }
    }


    public function __destruct() {
        // Close all FTP connections
        foreach (self::$ftpConnections as $conn) {
            ftp_close($conn);
        }
        self::$ftpConnections = [];
    }

    public function getConfig() {
        return $this->config;
    }
}
