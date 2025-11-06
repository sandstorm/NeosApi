import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import {getCurrentBaseWorkspace, getLoggedInUserName, makeReduxStoreAccessibleForTesting} from "./util/reduxStore";

function flow(command: string) {
  console.log("=== FLOW === " + command)
  return execSync('../../flow ' + command, { encoding: 'utf-8' });
}

test.beforeEach(async ({ page }) => {
  await makeReduxStoreAccessibleForTesting(page);
});



test('Log in via JWT basically works, and creates user at first call', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-1'));

  const jwtU1 = flow('sandstorm.neosapi:testingHelper:contentEditingUri test-1');
  await page.goto(jwtU1);
  expect(await getLoggedInUserName(page)).toBe('test-1');
});

test('Log in via JWT switches users if already logged in', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-1'));
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-2'));

  const jwtU1 = flow('sandstorm.neosapi:testingHelper:contentEditingUri test-1');
  const jwtU2 = flow('sandstorm.neosapi:testingHelper:contentEditingUri test-2');

  await page.goto(jwtU1);
  expect(await getLoggedInUserName(page)).toBe('test-1');
  await page.goto(jwtU2);
  expect(await getLoggedInUserName(page)).toBe('test-2');

  // we can switch back to test-1
  await page.goto(jwtU1);
  expect(await getLoggedInUserName(page)).toBe('test-1');
});

test('Log in via JWT can switch base workspace', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:ensureSharedWorkspaceExists review'));

  const jwtReviewWs = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithSwitchBaseWorkspace test-3 review');
  const jwtLiveWs = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithSwitchBaseWorkspace test-3 live');

  await page.goto(jwtReviewWs);
  expect(await getCurrentBaseWorkspace(page)).toBe('review');

  // we can switch back to live workspace
  await page.goto(jwtLiveWs);
  expect(await getCurrentBaseWorkspace(page)).toBe('live');
});
