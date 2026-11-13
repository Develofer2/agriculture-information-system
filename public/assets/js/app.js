(() => {
  const sidebar = document.querySelector("#sidebar");
  const toggle = document.querySelector("#sidebar-toggle");
  const mobile = document.querySelector(".mobile-menu");
  const backdrop = document.querySelector(".backdrop");
  const setCollapsed = (value) => {
    document.body.classList.toggle("sidebar-collapsed", value);
    toggle?.setAttribute("aria-expanded", String(!value));
    toggle?.setAttribute(
      "aria-label",
      value ? "Expand navigation" : "Collapse navigation",
    );
    localStorage.setItem("agri-sidebar-collapsed", String(value));
  };
  if (localStorage.getItem("agri-sidebar-collapsed") === "true")
    setCollapsed(true);
  toggle?.addEventListener("click", () =>
    setCollapsed(!document.body.classList.contains("sidebar-collapsed")),
  );
  const closeDrawer = () => document.body.classList.remove("drawer-open");
  mobile?.addEventListener("click", () =>
    document.body.classList.add("drawer-open"),
  );
  backdrop?.addEventListener("click", closeDrawer);
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeDrawer();
  });
  document.querySelectorAll("form[data-confirm]").forEach((form) =>
    form.addEventListener("submit", (e) => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    }),
  );
  document
    .querySelector("[data-print]")
    ?.addEventListener("click", () => window.print());
  const theme = localStorage.getItem("agri-theme");
  if (theme === "dark") document.documentElement.dataset.theme = "dark";
  document.querySelector("#theme-toggle")?.addEventListener("click", () => {
    const dark = document.documentElement.dataset.theme !== "dark";
    document.documentElement.dataset.theme = dark ? "dark" : "";
    localStorage.setItem("agri-theme", dark ? "dark" : "light");
  });
  document
    .querySelectorAll("form[data-dirty]")
    .forEach((form) =>
      form.addEventListener("input", () => (form.dataset.dirty = "true")),
    );
  if ("serviceWorker" in navigator)
    window.addEventListener("load", () =>
      navigator.serviceWorker.register("sw.js").catch(() => {}),
    );
})();
