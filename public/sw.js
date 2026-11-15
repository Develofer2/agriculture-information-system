const CACHE = "agri-static-v3";
const FILES = [
  "offline.html",
  "assets/css/app.css",
  "assets/js/app.js",
  "assets/icons/agri-is.svg",
];
self.addEventListener("install", (e) =>
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(FILES))),
);
self.addEventListener("activate", (e) =>
  e.waitUntil(
    caches
      .keys()
      .then((keys) =>
        Promise.all(
          keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)),
        ),
      ),
  ),
);
self.addEventListener("fetch", (e) => {
  const u = new URL(e.request.url);
  if (
    e.request.method !== "GET" ||
    u.origin !== location.origin ||
    u.pathname.endsWith(".php") ||
    u.pathname.includes("index.php")
  )
    return;
  e.respondWith(
    fetch(e.request).catch(() =>
      caches
        .match(e.request)
        .then(
          (x) =>
            x ||
            (e.request.mode === "navigate"
              ? caches.match("offline.html")
              : Response.error()),
        ),
    ),
  );
});
