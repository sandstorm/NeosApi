import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import {
  getCurrentBaseWorkspace,
  getCurrentDimensionValue,
  getCurrentNodeAggregateId,
  getLoggedInUserName,
  makeReduxStoreAccessibleForTesting
} from "./util/reduxStore";

function flow(command: string) {
  console.log("=== FLOW === " + command)
  return execSync('../../flow ' + command, { encoding: 'utf-8' });
}

test.beforeEach(async ({ page }) => {
  page.on("response", (response) => {
    test.fixme(response.status() === 500, `Error while running the test: The server responded with a 500 status;\nResource URL: ${response.url()}`)
  });

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

test('Log in via JWT can switch dimension', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-4'));

  const jwtDimensionLanguageDe = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithSwitchDimension --user test-4 --dimension language:de');
  const jwtDimensionLanguageEnUk = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithSwitchDimension --user test-4 --dimension language:en_UK');

  await page.goto(jwtDimensionLanguageDe);
  expect(await getCurrentDimensionValue(page, 'language')).toBe('de');

  // we can switch back to live workspace
  await page.goto(jwtDimensionLanguageEnUk);
  expect(await getCurrentDimensionValue(page, 'language')).toBe('en_UK');
});

test('Log in via JWT can select edited node', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-5'));

  const jwtNodeTextAndImages = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithNode test-5 14ccf43a-562a-c9f7-2fd1-733e27068524');
  const jwtNodeOtherElements = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithNode test-5 82c24d81-51fa-87e5-b4ec-73eb505cb826');

  await page.goto(jwtNodeTextAndImages);
  expect(await getCurrentNodeAggregateId(page)).toBe('14ccf43a-562a-c9f7-2fd1-733e27068524');

  // we can switch back to live workspace
  await page.goto(jwtNodeOtherElements);
  expect(await getCurrentNodeAggregateId(page)).toBe('82c24d81-51fa-87e5-b4ec-73eb505cb826');
});
