import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import vm from "node:vm";
import { test } from "vitest";

test("error page switches diagnostic tabs and toggles argument details", () => {
  const links = ["backtrace", "server"].map((id) => ({
    getAttribute: () => `#${id}`,
    blur() {},
    addEventListener(name, handler) {
      this[name] = handler;
    },
    className: "",
  }));

  const contents = { backtrace: {}, server: {}, "args.123": { style: {} } };
  const window = { getComputedStyle: () => ({ display: "none" }) };
  const escaped = [];
  vm.runInNewContext(
    readFileSync(new URL("../../../app/Views/errors/html/debug.js", import.meta.url), "utf8"),
    {
      window,
      CSS: {
        escape: (id) => {
          escaped.push(id);

          return id;
        },
      },
      document: {
        querySelectorAll: () => links,
        querySelector: (selector) => contents[selector.slice(1)],
      },
    },
  );
  window.init();
  assert.equal(links[0].className, "active");
  assert.equal(contents.server.className, "content hide");
  let prevented = false;
  links[1].click({
    preventDefault() {
      prevented = true;
    },
  });
  assert.equal(prevented, true);
  assert.equal(contents.backtrace.className, "content hide");
  assert.equal(contents.server.className, "content");
  assert.equal(window.toggle("args.123"), false);
  assert.equal(contents["args.123"].style.display, "block");
  window.toggle("args.123");
  assert.equal(contents["args.123"].style.display, "none");
  assert.ok(escaped.includes("args.123"));
});
