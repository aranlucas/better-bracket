import { format } from "@aranlucas/oxforge/format";

export default format({
  // These are unchanged CodeIgniter distribution files and runtime storage.
  ignorePatterns: [
    "app/Views/errors/html/debug.css",
    "app/Views/errors/html/debug.js",
    "writable/**",
  ],
  overrides: [{ files: ["composer.json"], options: { tabWidth: 4 } }],
});
