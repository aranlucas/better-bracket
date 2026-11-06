import { execFileSync } from "node:child_process";
import { randomUUID } from "node:crypto";
import { test } from "@e2e-dev/web";
import { credentials, expect } from "e2e";

// Contract: actual browser submissions must persist, rejected mutations must
// preserve data, and a corrected retry and logout must use the rotated CSRF token.
test("register, create a group, save and correct picks, then sign out and in", async ({
  app,
  screen,
  browser,
}) => {
  const email = `audit-${randomUUID()}@example.test`;
  const groupName = `Audit crew ${randomUUID()}`;
  await app.open("/login");
  await screen.getByLabel("First name").fill("Audit");
  await screen.getByLabel("Last name").fill("Player");
  await browser.locator("#register-email").fill(email);
  await browser.locator("#register-password").fill(credentials.user("player").password);
  await screen.getByRole("button", "Create account").tap();
  await expect(browser).toHaveURL("/dashboard");

  await app.open("/groups/new");
  await screen.getByLabel("Group name").fill(groupName);
  await screen.getByRole("button", "Create group").tap();
  await expect(screen.getByRole("heading", groupName)).toBeVisible();
  await expect(screen.getByRole("heading", "1 member")).toBeVisible();
  await app.open("/bracket");
  await screen.getByLabel("Save picks to").selectOption({ label: groupName });
  await browser.locator('[data-slot="1-1-1-1"]').tap();

  async function save(status: number) {
    const [response] = await Promise.all([
      browser.waitForResponse("**/bracket/picks"),
      screen.getByRole("button", "Save picks").tap(),
    ]);

    expect(response.status).toBe(status);
  }

  function savedTeams() {
    // Inspect persistence independently of the HTTP success message. The UUID
    // email is generated here, never supplied by an external user.
    return execFileSync(
      "psql",
      [
        "-At",
        "-c",
        `SELECT p.team_id FROM picks p JOIN users u ON u.id=p.user_id WHERE u.email='${email}' ORDER BY p.team_id`,
      ],
      { encoding: "utf8" },
    ).trim();
  }

  await save(200);
  await expect(screen.getByRole("status")).toHaveText("Picks saved.");
  expect(savedTeams()).toBe("1");

  await browser.route("**/bracket/picks", async (route) => {
    const body = new URLSearchParams(route.request.postData);
    body.set("picks", JSON.stringify({ "1-1-1-1": 999999 }));
    await route.continue({ postData: body.toString() });
  });
  await save(422);
  await expect(screen.getByRole("status")).toContainText("opening-round pick");
  expect(savedTeams()).toBe("1");
  await browser.unrouteAll();
  await browser.locator('[data-slot="1-1-1-2"]').tap();
  await save(200);
  await expect(screen.getByRole("status")).toHaveText("Picks saved.");
  expect(savedTeams()).toBe("16");

  // Preserve the public JSON contract, including controlled malformed-body errors.
  await browser.route("**/bracket/picks", async (route) => {
    const body = new URLSearchParams(route.request.postData);
    await route.continue({
      headers: { ...route.request.headers, "content-type": "application/json" },
      postData: JSON.stringify({
        group_id: body.get("group_id"),
        picks: JSON.parse(body.get("picks") ?? "null"),
      }),
    });
  });
  await save(200);
  await expect(screen.getByRole("status")).toHaveText("Picks saved.");
  await browser.unrouteAll();
  await browser.route("**/bracket/picks", async (route) => {
    await route.continue({
      headers: { ...route.request.headers, "content-type": "application/json" },
      postData: "{",
    });
  });
  await save(422);
  await expect(screen.getByRole("status")).toContainText("valid JSON");
  expect(savedTeams()).toBe("16");
  await browser.unrouteAll();
  await save(200);
  await expect(screen.getByRole("status")).toHaveText("Picks saved.");

  await screen.getByRole("button", "Sign out").tap();
  await expect(browser).toHaveURL("/login");
  await browser.locator("#login-email").fill(email);
  await browser.locator("#login-password").fill(credentials.user("player").password);
  await screen.getByRole("button", "Sign in", { exact: false }).tap();
  await expect(browser).toHaveURL("/dashboard");
});
