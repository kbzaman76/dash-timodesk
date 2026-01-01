(async function () {
    if (typeof TOKEN_ROUTE == "undefined") {
        console.error("TOKEN_ROUTE not defined");
        return;
    }

    if (typeof io == "undefined") {
        console.error("Socket.IO not loaded");
        return;
    }

    const SOCKET_URL = "https://wss.stakegame.net";
    const SOCKET_PATH = "/timo-socket/socket.io";

    let socket;
    let tokenData;

    // Fetch token from web route
    async function fetchToken() {
        try {
            const res = await fetch(TOKEN_ROUTE, {
                headers: { Accept: "application/json" },
                credentials: "include", // Laravel session cookie
            });

            if (!res.ok) throw new Error("Failed to fetch socket token");

            tokenData = await res.json();
            if (!tokenData?.token) throw new Error("Token missing in response");
        } catch (err) {
            console.error("Error fetching socket token:", err);
        }
    }

    // Connect socket
    async function connectSocket() {
        if (!tokenData?.token) return;

        socket = io(SOCKET_URL, {
            transports: ["websocket"],
            path: SOCKET_PATH,
            auth: {
                token: tokenData.token,
                org_id: tokenData.org_id,
            },
        });

        socket.on("user:status", ({ userId, orgId, status }) => {
            if (!userId || orgId != tokenData.org_id) return;
            updateOnlineUI(status);
        });

        socket.on("connect_error", async (err) => {
            if (err.message == "Unauthorized") {
                console.warn("Socket token expired, refreshing...");
                await refreshSocketToken();
            }
        });
    }

    function updateOnlineUI(status) {
        const $el = $(".dashboard-online-users");
        let old = Number($el.text());
        old = status == "online" ? old + 1 : old - 1;
        $el.text(Math.max(old, 0));
    }

    // Refresh token & reconnect
    async function refreshSocketToken() {
        if (socket) {
            socket.disconnect();
        }
        await fetchToken();
        await connectSocket();
    }

    // Initial load
    await fetchToken();
    await connectSocket();
})();
