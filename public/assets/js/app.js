(function () {
  "use strict";

  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector(".main-nav");

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      const open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(open));
    });
  }

  for (const flash of document.querySelectorAll(".flash")) {
    window.setTimeout(function () {
      flash.style.opacity = "0";
      window.setTimeout(function () {
        flash.remove();
      }, 220);
    }, 6500);
  }
})();
