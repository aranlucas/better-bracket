(function () {
  "use strict";

  const tabs = [];

  function selectTab(selected) {
    for (const { link, content } of tabs) {
      link.className = link === selected ? "active" : "";
      content.className = link === selected ? "content" : "content hide";
    }
  }

  window.init = function () {
    for (const link of document.querySelectorAll("#tabs li a")) {
      const id = link.getAttribute("href").split("#").pop();
      tabs.push({ link, content: document.querySelector(`#${CSS.escape(id)}`) });
      link.addEventListener("click", function (event) {
        event.preventDefault();
        selectTab(link);

      });

      link.addEventListener("focus", function () {
        link.blur();
      });
    }

    selectTab(tabs[0]?.link);
  };

  window.toggle = function (id) {
    const element = document.querySelector(`#${CSS.escape(id)}`);
    const display = element.style.display || window.getComputedStyle(element).display;
    element.style.display = display === "block" ? "none" : "block";

    return false;
  };
})();
