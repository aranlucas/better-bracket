import assert from "node:assert/strict";
import { spawnSync } from "node:child_process";
import { mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";
import { test } from "vitest";

const { scripts } = JSON.parse(
  readFileSync(new URL("../../composer.json", import.meta.url), "utf8"),
);

test("PHP syntax checking fails for malformed files and accepts paths containing spaces", () => {
  const directory = mkdtempSync(join(tmpdir(), "better-bracket-lint-"));

  try {
    for (const name of ["app", "public", "test"]) {
      mkdirSync(join(directory, name));
    }

    writeFileSync(join(directory, "app", "valid file.php"), '<?php echo "ok";\n');
    const valid = spawnSync("sh", ["-c", scripts.lint], { cwd: directory, encoding: "utf8" });
    assert.equal(valid.status, 0, valid.stderr);
    assert.match(valid.stdout, /No syntax errors detected in app\/valid file\.php/);

    writeFileSync(join(directory, "test", "broken.php"), "<?php function broken( {\n");
    const invalid = spawnSync("sh", ["-c", scripts.lint], { cwd: directory, encoding: "utf8" });
    assert.equal(invalid.status, 1, "Syntax errors must fail the Composer lint gate");
    assert.match(invalid.stdout + invalid.stderr, /Errors parsing test\/broken\.php/);
  } finally {
    rmSync(directory, { recursive: true, force: true });
  }
});
