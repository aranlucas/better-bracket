import type { E2EConfig } from "e2e";
import { web } from "@e2e-dev/web";

export default {
  tests: "test/e2e/**/*.e2e.ts",
  targets: [
    {
      name: "chromium",
      engine: web(),
      app: {
        url: "http://127.0.0.1:8080",
        command: {
          executable: "php",
          args: ["spark", "serve", "--host", "127.0.0.1", "--port", "8080"],
          inheritEnv: ["CI_ENVIRONMENT", "app_*", "database_*"],
          log: ".e2e/logs/app.log",
        },
      },
    },
  ],
  workers: 1,
  retries: 0,
  timeout: 120_000,
  reporters: ["list", "junit"],
  credentials: { player: { username: "fixture", password: "ci-only-password" } },
} satisfies E2EConfig;
