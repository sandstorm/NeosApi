import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';
import {
  getCurrentBaseWorkspace,
  getCurrentDimensionValue,
  getCurrentNodeAggregateId,
  getCurrentPreviewMode,
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

test('Log in via JWT can create nodes if they do not yet exist', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-6'));
  console.log(flow('sandstorm.neosapi:testingHelper:removeNodeIfExists test-6 a399a3ce-4923-4097-a3d4-2e291e22a1fc'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithCreateNodeIfNotExists' +
    ' --user=test-6' +
    ' --nodeAggregateId=a399a3ce-4923-4097-a3d4-2e291e22a1fc' +
    ' --nodeType=Neos.Demo:Document.Page' +
    ' --parentNodeAggregateId=a66ec7db-3459-b67b-7bcb-16e2508a89f0');

  await page.goto(jwtNode);
  expect(await getCurrentNodeAggregateId(page)).toBe('a399a3ce-4923-4097-a3d4-2e291e22a1fc');
});

test('The order of JWT LoginCommands should not change their semantics', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-7'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingUriNodeSelectionBeforeDimensionSelection' +
    ' --user=test-7' +
    ' --nodeAggregateId=d87adae6-9e61-4dbc-b596-219abe1e45a2' +
    ' --dimension=language:de');

  await expect(async () => {
    const response = await page.request.get(jwtNode, { timeout: 2_000, maxRedirects: 0 });
    expect(response.status()).toBe(400);
  }).toPass({ timeout: 2_000 });
});

test('Log in via JWT can hide the main menu', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-8'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingWithHiddenMainMenu' +
    ' --user=test-8');

  await page.goto(jwtNode);
  // wait for page to load
  await expect(page.locator('#neos-application')).toHaveCount(1);
  // actual test case
  await expect(page.locator('#neos-MenuToggler')).toHaveCount(0);
});

test('Log in via JWT can hide the sidebar', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-8'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingWithHiddenLeftSideBar' +
    ' --user=test-8');

  await page.goto(jwtNode);
  // wait for page to load
  await expect(page.locator('#neos-application')).toHaveCount(1);
  // actual test case
  await expect(page.locator('#neos-LeftSideBarToggler')).toHaveCount(0);
});

test('Log in via JWT can hide the edit preview mode drop down', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-9'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithHiddenEditPreviewModeDropDown' +
    ' --user=test-9');

  await page.goto(jwtNode);
  // wait for page to load
  await expect(page.locator('#neos-application')).toHaveCount(1);
  // actual test case
  await expect(page.locator("xpath=//span[contains(@class, 'dropDown__currentEditMode')]")).toHaveCount(0);
});

test('Log in via JWT can reduce the editing ui to the bare minimum', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-minimal-ui'));

  const jwtNode = flow('sandstorm.neosapi:testingHelper:contentEditingWithMinimalUi' +
    ' --user=test-minimal-ui');

  await page.goto(jwtNode);
  // wait for page to load
  await expect(page.locator('#neos-application')).toHaveCount(1);
  // actual test cases
  await expect(page.locator('#neos-MenuToggler')).toHaveCount(0);
  await expect(page.locator('#neos-LeftSideBarToggler')).toHaveCount(0);
  await expect(page.locator("xpath=//span[contains(@class, 'dropDown__currentEditMode')]")).toHaveCount(0);
});

test('Log in via JWT can set the preview mode', async ({ page }) => {
  console.log(flow('sandstorm.neosapi:testingHelper:removeUserIfExists test-preview-mode'));

  const jwtNodeMobile = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithPreviewMode' +
    ' --user=test-preview-mode' +
    ' --previewMode=mobile');

  const jwtNodeDesktop = flow('sandstorm.neosapi:testingHelper:contentEditingUriWithPreviewMode' +
    ' --user=test-preview-mode' +
    ' --previewMode=desktop');

  await page.goto(jwtNodeMobile);
  expect(await getCurrentPreviewMode(page)).toBe('mobile');

  await page.goto(jwtNodeDesktop);
  expect(await getCurrentPreviewMode(page)).toBe('desktop');
});
