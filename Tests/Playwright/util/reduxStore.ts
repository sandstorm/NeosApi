import {expect, Page} from "@playwright/test";

export async function makeReduxStoreAccessibleForTesting(page: Page) {
  await page.addInitScript(() => {
    // Create a proxy for the DevTools extension
    const originalDevTools = window.__REDUX_DEVTOOLS_EXTENSION__;

    window.__REDUX_DEVTOOLS_EXTENSION__ = function () {
      // Call original if it exists
      const enhancer = originalDevTools ? originalDevTools() : (f => f);

      // Return a wrapped enhancer that captures the store
      return (createStore) => {
        return (reducer, preloadedState) => {
          const store = enhancer(createStore)(reducer, preloadedState);

          // Expose the store
          window.__PLAYWRIGHT_REDUX_STORE__ = store;

          return store;
        };
      };
    };

    // Keep the extension "present" for your check
    window.__REDUX_DEVTOOLS_EXTENSION__.open = () => {
    };
  });
}

export async function getReduxState(page: Page): Promise<any> {
  return await page.evaluate(() => {
    const store = window.__PLAYWRIGHT_REDUX_STORE__ ||
      window.__REDUX_DEVTOOLS_EXTENSION__?.store;

    if (!store) {
      throw new Error('Redux store not found');
    }

    return store.getState();
  });
}

export async function getLoggedInUserName(page: Page): Promise<string | undefined> {
  const state = await getReduxState(page);
  // see @connect in Neos.Neos.Ui/packages/neos-ui/src/Containers/Drawer/UserDropDown/index.js
  return state?.user?.name?.fullName;
}

