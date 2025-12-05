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

export async function getCurrentBaseWorkspace(page: Page): Promise<string | undefined> {
  const state = await getReduxState(page);
  // see Neos.Neos.Ui/packages/neos-ui-redux-store/src/CR/Workspaces/selectors.ts
  return state?.cr?.workspaces?.personalWorkspace?.baseWorkspace
}

export async function getCurrentDimensionValue(page: Page, dimension): Promise<string | undefined> {
  const state = await getReduxState(page);
  // see Neos.Neos.Ui/packages/neos-ui-redux-store/src/CR/ContentDimensions/index.ts
  return state?.cr?.contentDimensions.active[dimension][0];
}

export async function getCurrentNodeAggregateId(page: Page): Promise<string | undefined> {
  const state = await getReduxState(page);
  // see Neos.Neos.Ui/packages/neos-ui-redux-store/src/CR/Nodes/selectors.ts
  return state?.cr?.nodes?.byContextPath[state?.cr?.nodes?.documentNode].identifier;
}

export async function getCurrentPreviewMode(page: Page): Promise<string | undefined> {
  const state = await getReduxState(page);
  // see Neos.Neos.Ui/packages/neos-ui-redux-store/src/UI/EditPreviewMode/index.ts
  return state?.ui?.editPreviewMode;
}
