/* Listener de prueba: recibe canales privados por argv y loguea eventos. */
process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
const http = require("http");
global.window = global;
const Echo = require("laravel-echo").default || require("laravel-echo");
window.Pusher = require("pusher-js");
window.Pusher.logToConsole = true;

const echo = new Echo({
  broadcaster: "pusher",
  key: "ABCABC12345",
  wsHost: "127.0.0.1",
  wsPort: 6001,
  forceTLS: false,
  encrypted: false,
  disableStats: true,
  enabledTransports: ["ws", "wss"],
  cluster: "mt1",
  authorizer: (channel) => ({
    authorize: (socketId, callback) => {
      const data =
        "socket_id=" +
        encodeURIComponent(socketId) +
        "&channel_name=" +
        encodeURIComponent(channel.name);
      const req = http.request(
        {
          host: "127.0.0.1",
          port: 8000,
          path: "/api/v1/broadcasting/auth",
          method: "POST",
          headers: {
            Authorization: "Bearer " + process.env.TEST_TOKEN,
            "Content-Type": "application/x-www-form-urlencoded",
            Accept: "application/json",
            "Content-Length": Buffer.byteLength(data),
          },
        },
        (res) => {
          let b = "";
          res.on("data", (c) => (b += c));
          res.on("end", () => {
            try {
              callback(null, JSON.parse(b));
            } catch (err) {
              callback(new Error("auth resp: " + b));
            }
          });
        }
      );
      req.on("error", (e) => callback(e));
      req.write(data);
      req.end();
    },
  }),
});

process.argv.slice(2).forEach((raw) => {
  const ch = raw.replace(/^private-/, "");
  const c = echo.private(ch);
  c.listen(".message.received", (e) =>
    console.log("PREVIEW_EVENT " + JSON.stringify(e))
  );
  c.listen(".App\\Events\\NewNotificationEvent", (e) =>
    console.log("NOTIF_EVENT gen=" + e.id_generator + " body=" + e.body)
  );
  c.listen(".messages.read", (e) =>
    console.log("READ_EVENT gens=" + JSON.stringify(e.generator_ids))
  );
});
console.log("LISTENING " + process.argv.slice(2).join(","));
