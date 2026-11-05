/**
 * Layanan WhatsApp memakai Baileys (WhatsApp Web multi-device).
 *
 * Alur:
 *   1. Jalankan `npm start`, lalu scan QR yang muncul dengan nomor pengirim.
 *   2. Laravel mengirim HTTP POST ke /send: { to, message, image_url? }.
 *   3. Layanan ini mengirim pesan (opsional dengan gambar) ke pelanggan.
 *
 * Variabel lingkungan:
 *   WA_PORT           (default 3001)
 *   WA_SERVICE_TOKEN  (opsional; jika diisi, wajib dikirim sebagai Bearer token)
 *   WA_AUTH_DIR       (default ./auth)
 */

const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
} = require("@whiskeysockets/baileys");

const express = require("express");
const qrcode = require("qrcode-terminal");
const pino = require("pino");
const NodeCache = require("node-cache");

const PORT = Number(process.env.WA_PORT || 3001);
const TOKEN = process.env.WA_SERVICE_TOKEN || "";
const AUTH_DIR = process.env.WA_AUTH_DIR || "./auth";
const MAX_IMAGE_BYTES = 8 * 1024 * 1024;

const app = express();
app.use(express.json({ limit: "12mb" }));

const msgRetryCounterCache = new NodeCache();

let sock = null;
let isReady = false;
let isInitializing = false;
let initPromise = null;

async function startSock() {
  if (isInitializing) {
    return initPromise;
  }

  isInitializing = true;
  isReady = false;

  initPromise = (async () => {
    try {
      const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
      const { version } = await fetchLatestBaileysVersion();

      sock = makeWASocket({
        version,
        logger: pino({ level: "silent" }),
        auth: state,
        msgRetryCounterCache,
        printQRInTerminal: false,
      });

      sock.ev.on("creds.update", saveCreds);

      sock.ev.on("connection.update", (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
          console.log("📱 Scan QR berikut dengan WhatsApp nomor pengirim:");
          qrcode.generate(qr, { small: true });
        }

        if (connection === "open") {
          isReady = true;
          console.log("✅ WhatsApp siap digunakan.");
        }

        if (connection === "close") {
          isReady = false;

          const statusCode = lastDisconnect?.error?.output?.statusCode;
          const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

          console.log("⚠️  Koneksi terputus.", shouldReconnect ? "Menyambung ulang..." : "Sudah logout, scan QR ulang.");

          if (shouldReconnect) {
            setTimeout(() => startSock(), 5000);
          }
        }
      });

      isInitializing = false;
      initPromise = null;
    } catch (err) {
      console.error("❌ Gagal inisialisasi:", err.message);
      isInitializing = false;
      initPromise = null;

      console.log("🔄 Coba lagi dalam 10 detik...");
      setTimeout(() => startSock(), 10000);
    }
  })();

  return initPromise;
}

async function sendWithRetry(fn, retries = 3, delay = 3000) {
  let lastError;

  for (let attempt = 1; attempt <= retries; attempt++) {
    try {
      return await fn();
    } catch (err) {
      lastError = err;
      console.log(`🔄 Percobaan ${attempt}/${retries} gagal: ${err.message}`);

      if (attempt < retries) {
        await new Promise((resolve) => setTimeout(resolve, delay));
      }
    }
  }

  throw lastError;
}

async function resolveImage(image, imageUrl) {
  if (image) {
    return Buffer.from(image, "base64");
  }

  if (!imageUrl) {
    return null;
  }

  const response = await fetch(imageUrl);

  if (!response.ok) {
    throw new Error(`Gagal mengambil gambar (HTTP ${response.status})`);
  }

  const buffer = Buffer.from(await response.arrayBuffer());

  if (buffer.length > MAX_IMAGE_BYTES) {
    throw new Error("Ukuran gambar terlalu besar.");
  }

  return buffer;
}

function requireToken(req, res, next) {
  if (!TOKEN) {
    return next();
  }

  if (req.headers.authorization !== `Bearer ${TOKEN}`) {
    return res.status(401).json({ success: false, message: "Unauthorized" });
  }

  return next();
}

app.get("/status", (_req, res) => {
  res.json({ ready: isReady });
});

app.post("/send", requireToken, async (req, res) => {
  const { to, message, image_url: imageUrl, image } = req.body || {};

  if (!to || !message) {
    return res.status(422).json({ success: false, message: "Field 'to' dan 'message' wajib diisi." });
  }

  if (!isReady || !sock) {
    return res.status(503).json({ success: false, message: "WhatsApp belum siap." });
  }

  try {
    const jid = `${String(to).replace(/\D/g, "")}@s.whatsapp.net`;
    const imageBuffer = await resolveImage(image, imageUrl);

    if (imageBuffer) {
      await sendWithRetry(() =>
        sock.sendMessage(jid, { image: imageBuffer, caption: message })
      );
    } else {
      await sendWithRetry(() => sock.sendMessage(jid, { text: message }));
    }

    console.log("✅ Pesan terkirim ke", to);

    return res.json({ success: true });
  } catch (err) {
    console.error("❌ Gagal mengirim pesan:", err.message);

    return res.status(500).json({ success: false, message: err.message });
  }
});

process.on("uncaughtException", (err) => {
  console.error("Uncaught Exception:", err);
});

process.on("unhandledRejection", (err) => {
  console.error("Unhandled Rejection:", err);
});

startSock().then(() => {
  app.listen(PORT, () => {
    console.log(`🚀 wa-service berjalan di http://127.0.0.1:${PORT}`);
  });
});
