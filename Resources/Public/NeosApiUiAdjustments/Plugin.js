(() => {
  var __create = Object.create;
  var __defProp = Object.defineProperty;
  var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __getProtoOf = Object.getPrototypeOf;
  var __hasOwnProp = Object.prototype.hasOwnProperty;
  var __esm = (fn, res) => function __init() {
    return fn && (res = (0, fn[__getOwnPropNames(fn)[0]])(fn = 0)), res;
  };
  var __commonJS = (cb, mod) => function __require() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };
  var __copyProps = (to, from, except, desc) => {
    if (from && typeof from === "object" || typeof from === "function") {
      for (let key of __getOwnPropNames(from))
        if (!__hasOwnProp.call(to, key) && key !== except)
          __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
    }
    return to;
  };
  var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
    // If the importer is in node compatibility mode or this is not an ESM
    // file that has been converted to a CommonJS file using a Babel-
    // compatible transform (i.e. "__esModule" has not been set), then set
    // "default" to the CommonJS "module.exports" for node compatibility.
    isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
    mod
  ));
  var __decorateClass = (decorators, target, key, kind) => {
    var result = kind > 1 ? void 0 : kind ? __getOwnPropDesc(target, key) : target;
    for (var i = decorators.length - 1, decorator; i >= 0; i--)
      if (decorator = decorators[i])
        result = (kind ? decorator(target, key, result) : decorator(result)) || result;
    if (kind && result)
      __defProp(target, key, result);
    return result;
  };

  // node_modules/@neos-project/neos-ui-extensibility/dist/manifest.js
  var init_manifest = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/manifest.js"() {
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/createConsumerApi.js
  var init_createConsumerApi = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/createConsumerApi.js"() {
      init_manifest();
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/readFromConsumerApi.js
  function readFromConsumerApi(key) {
    return (...args) => {
      if (window["@Neos:HostPluginAPI"] && window["@Neos:HostPluginAPI"][`@${key}`]) {
        return window["@Neos:HostPluginAPI"][`@${key}`](...args);
      }
      throw new Error("You are trying to read from a consumer api that hasn't been initialized yet!");
    };
  }
  var init_readFromConsumerApi = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/readFromConsumerApi.js"() {
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/registry/AbstractRegistry.js
  var init_AbstractRegistry = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/registry/AbstractRegistry.js"() {
    }
  });

  // node_modules/@neos-project/positional-array-sorter/dist/positionalArraySorter.js
  var init_positionalArraySorter = __esm({
    "node_modules/@neos-project/positional-array-sorter/dist/positionalArraySorter.js"() {
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/registry/SynchronousRegistry.js
  var init_SynchronousRegistry = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/registry/SynchronousRegistry.js"() {
      init_AbstractRegistry();
      init_positionalArraySorter();
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/registry/SynchronousMetaRegistry.js
  var init_SynchronousMetaRegistry = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/registry/SynchronousMetaRegistry.js"() {
      init_SynchronousRegistry();
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/registry/index.js
  var init_registry = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/registry/index.js"() {
      init_SynchronousRegistry();
      init_SynchronousMetaRegistry();
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/index.js
  var dist_default;
  var init_dist = __esm({
    "node_modules/@neos-project/neos-ui-extensibility/dist/index.js"() {
      init_createConsumerApi();
      init_readFromConsumerApi();
      init_registry();
      dist_default = readFromConsumerApi("manifest");
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/react/index.js
  var require_react = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/react/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("vendor")().React;
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/neos-ui-decorators/index.js
  var require_neos_ui_decorators = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/neos-ui-decorators/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("NeosProjectPackages")().NeosUiDecorators;
    }
  });

  // Resources/Private/NeosApiUiAdjustments/WrappedMenuToggler.js
  function wrappedMenuTogglerFactory(OriginalMenuToggler) {
    class WrappedMenuToggler extends import_react.PureComponent {
      render() {
        const { showMainMenu } = this.props;
        if (showMainMenu) {
          return /* @__PURE__ */ import_react.default.createElement(OriginalMenuToggler, null);
        }
        return null;
      }
    }
    return (0, import_neos_ui_decorators.neos)((globalRegistry) => ({
      showMainMenu: globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi").showMainMenu
    }))(WrappedMenuToggler);
  }
  var import_react, import_neos_ui_decorators;
  var init_WrappedMenuToggler = __esm({
    "Resources/Private/NeosApiUiAdjustments/WrappedMenuToggler.js"() {
      import_react = __toESM(require_react());
      import_neos_ui_decorators = __toESM(require_neos_ui_decorators());
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/prop-types/index.js
  var require_prop_types = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/prop-types/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("vendor")().PropTypes;
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/classnames/index.js
  var require_classnames = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/classnames/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("vendor")().classnames;
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/react-redux/index.js
  var require_react_redux = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/react-redux/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("vendor")().reactRedux;
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/neos-ui-redux-store/index.js
  var require_neos_ui_redux_store = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/neos-ui-redux-store/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("NeosProjectPackages")().NeosUiReduxStore;
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/react-ui-components/index.js
  var require_react_ui_components = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/neosProjectPackages/react-ui-components/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("NeosProjectPackages")().ReactUiComponents;
    }
  });

  // Resources/Private/NeosApiUiAdjustments/index.css
  var _default;
  var init_ = __esm({
    "Resources/Private/NeosApiUiAdjustments/index.css"() {
      _default = {};
    }
  });

  // Resources/Private/NeosApiUiAdjustments/LeftSideBarWithoutDocumentTree.js
  var import_react2, import_prop_types, import_classnames, import_react_redux, import_neos_ui_redux_store, import_react_ui_components, import_neos_ui_decorators2, LeftSideBarWithoutDocumentTree;
  var init_LeftSideBarWithoutDocumentTree = __esm({
    "Resources/Private/NeosApiUiAdjustments/LeftSideBarWithoutDocumentTree.js"() {
      import_react2 = __toESM(require_react());
      import_prop_types = __toESM(require_prop_types());
      import_classnames = __toESM(require_classnames());
      import_react_redux = __toESM(require_react_redux());
      import_neos_ui_redux_store = __toESM(require_neos_ui_redux_store());
      import_react_ui_components = __toESM(require_react_ui_components());
      import_neos_ui_decorators2 = __toESM(require_neos_ui_decorators());
      init_();
      LeftSideBarWithoutDocumentTree = class extends import_react2.PureComponent {
        constructor() {
          super(...arguments);
          this.handleToggle = () => {
            const { toggleSidebar } = this.props;
            toggleSidebar();
          };
        }
        render() {
          const { isHidden, isFullScreen, isHiddenContentTree, containerRegistry, i18nRegistry } = this.props;
          const classNames = (0, import_classnames.default)({
            [_default.leftSideBar]: true,
            [_default["leftSideBar--isHidden"]]: isHidden || isFullScreen
          });
          const contentTreeWrapperClassNames = (0, import_classnames.default)({
            [_default.leftSideBar__top]: true
          });
          const LeftSideBarBottom = containerRegistry.getChildren("LeftSideBar/Bottom");
          const ContentTreeToolbar = containerRegistry.get("LeftSideBar/ContentTreeToolbar");
          const toggleIcon = isHidden ? "chevron-circle-right" : "chevron-circle-left";
          const toggle = isFullScreen ? null : /* @__PURE__ */ import_react2.default.createElement(
            import_react_ui_components.IconButton,
            {
              id: "neos-LeftSideBarToggler",
              icon: toggleIcon,
              className: _default.leftSideBar__toggleBtn,
              hoverStyle: "clean",
              title: i18nRegistry.translate("Neos.Neos:Main:navigate")
            }
          );
          return /* @__PURE__ */ import_react2.default.createElement(import_react2.default.Fragment, null, /* @__PURE__ */ import_react2.default.createElement("div", { role: "button", className: _default.leftSideBar__header, onClick: this.handleToggle }, toggle, !isHidden && !isFullScreen && i18nRegistry.translate("Neos.Neos:Main:documentTree", "Document Tree")), /* @__PURE__ */ import_react2.default.createElement(
            import_react_ui_components.SideBar,
            {
              position: "left",
              className: classNames,
              "aria-hidden": isHidden ? "true" : "false"
            },
            /* @__PURE__ */ import_react2.default.createElement("div", { className: contentTreeWrapperClassNames }, /* @__PURE__ */ import_react2.default.createElement(ContentTreeToolbar, null), !isHidden && !isHiddenContentTree && LeftSideBarBottom.map((Item, key) => /* @__PURE__ */ import_react2.default.createElement(Item, { key })))
          ));
        }
      };
      LeftSideBarWithoutDocumentTree.propTypes = {
        containerRegistry: import_prop_types.default.object.isRequired,
        i18nRegistry: import_prop_types.default.object.isRequired,
        isHidden: import_prop_types.default.bool.isRequired,
        isHiddenContentTree: import_prop_types.default.bool.isRequired,
        toggleSidebar: import_prop_types.default.func.isRequired
      };
      LeftSideBarWithoutDocumentTree = __decorateClass([
        (0, import_neos_ui_decorators2.neos)((globalRegistry) => ({
          containerRegistry: globalRegistry.get("containers"),
          i18nRegistry: globalRegistry.get("i18n")
        })),
        (0, import_react_redux.connect)((state) => ({
          isHidden: state?.ui?.leftSideBar?.isHidden,
          isFullScreen: state?.ui?.fullScreen?.isFullScreen,
          isHiddenContentTree: state?.ui?.leftSideBar?.contentTree?.isHidden,
          siteNode: import_neos_ui_redux_store.selectors.CR.Nodes.siteNodeSelector(state),
          documentNode: import_neos_ui_redux_store.selectors.CR.Nodes.documentNodeSelector(state)
        }), {
          toggleSidebar: import_neos_ui_redux_store.actions.UI.LeftSideBar.toggle
        })
      ], LeftSideBarWithoutDocumentTree);
    }
  });

  // Resources/Private/NeosApiUiAdjustments/WrappedLeftSideBar.js
  function wrappedLeftSideBarFactory(OriginalLeftSideBar) {
    class WrappedLeftSideBar extends import_react3.PureComponent {
      render() {
        const { showLeftSideBar, showDocumentTree } = this.props;
        if (showLeftSideBar && showDocumentTree) {
          return /* @__PURE__ */ import_react3.default.createElement(OriginalLeftSideBar, null);
        } else if (showLeftSideBar && !showDocumentTree) {
          return /* @__PURE__ */ import_react3.default.createElement(LeftSideBarWithoutDocumentTree, null);
        }
        return null;
      }
    }
    return (0, import_neos_ui_decorators3.neos)((globalRegistry) => ({
      showLeftSideBar: globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi").showLeftSideBar,
      showDocumentTree: globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi").showDocumentTree
    }))(WrappedLeftSideBar);
  }
  var import_react3, import_neos_ui_decorators3;
  var init_WrappedLeftSideBar = __esm({
    "Resources/Private/NeosApiUiAdjustments/WrappedLeftSideBar.js"() {
      import_react3 = __toESM(require_react());
      import_neos_ui_decorators3 = __toESM(require_neos_ui_decorators());
      init_LeftSideBarWithoutDocumentTree();
    }
  });

  // Resources/Private/NeosApiUiAdjustments/WrappedEditPreviewDropDown.js
  function wrappedEditPreviewDropDownFactory(OriginalEditPreviewDropDown) {
    class WrappedEditPreviewDropDown extends import_react4.PureComponent {
      render() {
        const { showEditPreviewDropDown } = this.props;
        if (showEditPreviewDropDown) {
          return /* @__PURE__ */ import_react4.default.createElement(OriginalEditPreviewDropDown, null);
        }
        return null;
      }
    }
    return (0, import_neos_ui_decorators4.neos)((globalRegistry) => ({
      showEditPreviewDropDown: globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi").showEditPreviewDropDown
    }))(WrappedEditPreviewDropDown);
  }
  var import_react4, import_neos_ui_decorators4;
  var init_WrappedEditPreviewDropDown = __esm({
    "Resources/Private/NeosApiUiAdjustments/WrappedEditPreviewDropDown.js"() {
      import_react4 = __toESM(require_react());
      import_neos_ui_decorators4 = __toESM(require_neos_ui_decorators());
    }
  });

  // Resources/Private/NeosApiUiAdjustments/WrappedComponent.tsx
  function wrappedComponentFactory(selector) {
    return function(OriginalComponent) {
      class WrappedComponent extends import_react5.PureComponent {
        render() {
          const { showComponent } = this.props;
          if (showComponent) {
            return /* @__PURE__ */ import_react5.default.createElement(OriginalComponent, null);
          }
          return null;
        }
      }
      return (0, import_neos_ui_decorators5.neos)((globalRegistry) => ({
        showComponent: selector(globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi"))
      }))(WrappedComponent);
    };
  }
  var import_react5, import_neos_ui_decorators5;
  var init_WrappedComponent = __esm({
    "Resources/Private/NeosApiUiAdjustments/WrappedComponent.tsx"() {
      import_react5 = __toESM(require_react());
      import_neos_ui_decorators5 = __toESM(require_neos_ui_decorators());
    }
  });

  // Resources/Private/NeosApiUiAdjustments/WrappedDimensionSwitcher.js
  var wrappedDimensionSwitcherFactory;
  var init_WrappedDimensionSwitcher = __esm({
    "Resources/Private/NeosApiUiAdjustments/WrappedDimensionSwitcher.js"() {
      init_WrappedComponent();
      wrappedDimensionSwitcherFactory = wrappedComponentFactory((config) => config.showDimensionSwitcher);
    }
  });

  // node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/redux-saga-effects/index.js
  var require_redux_saga_effects = __commonJS({
    "node_modules/@neos-project/neos-ui-extensibility/dist/shims/vendor/redux-saga-effects/index.js"(exports, module) {
      init_readFromConsumerApi();
      module.exports = readFromConsumerApi("vendor")().reduxSagaEffects;
    }
  });

  // Resources/Private/NeosApiUiAdjustments/notifyOnPublishSaga.js
  function* notifyOnPublishSaga({ globalRegistry }) {
    const targetOrigin = globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi").notifyOnPublishTarget;
    if (targetOrigin) {
      const notifyOnPublish = notifyOnPublishWithTargetOrigin(targetOrigin);
      yield (0, import_effects.takeEvery)(import_neos_ui_redux_store2.actionTypes.CR.Publishing.FINISHED, notifyOnPublish);
    }
  }
  var import_effects, import_neos_ui_redux_store2, notifyOnPublishWithTargetOrigin;
  var init_notifyOnPublishSaga = __esm({
    "Resources/Private/NeosApiUiAdjustments/notifyOnPublishSaga.js"() {
      import_effects = __toESM(require_redux_saga_effects());
      import_neos_ui_redux_store2 = __toESM(require_neos_ui_redux_store());
      notifyOnPublishWithTargetOrigin = (targetOrigin) => function* (action) {
        yield (0, import_effects.call)(() => parent.postMessage(action, targetOrigin));
      };
    }
  });

  // Resources/Private/NeosApiUiAdjustments/manifest.js
  var require_manifest = __commonJS({
    "Resources/Private/NeosApiUiAdjustments/manifest.js"() {
      init_dist();
      init_WrappedMenuToggler();
      init_WrappedLeftSideBar();
      init_WrappedEditPreviewDropDown();
      init_WrappedDimensionSwitcher();
      init_notifyOnPublishSaga();
      dist_default("Sandstorm.NeosApi", {}, (globalRegistry) => {
        const containerRegistry = globalRegistry.get("containers");
        const wrapContainer = (name, wrapperFactory) => {
          const OriginalContainer = containerRegistry.get(name);
          containerRegistry.set(name, wrapperFactory(OriginalContainer));
        };
        wrapContainer("PrimaryToolbar/Left/MenuToggler", wrappedMenuTogglerFactory);
        wrapContainer("LeftSideBar", wrappedLeftSideBarFactory);
        wrapContainer("PrimaryToolbar/Right/EditPreviewDropDown", wrappedEditPreviewDropDownFactory);
        wrapContainer("PrimaryToolbar/Right/DimensionSwitcher", wrappedDimensionSwitcherFactory);
        const sagaRegistry = globalRegistry.get("sagas");
        sagaRegistry.set("Sandstorm:NeosApi:notifyOnPublishSaga", { saga: notifyOnPublishSaga });
      });
    }
  });

  // Resources/Private/NeosApiUiAdjustments/index.js
  var require_NeosApiUiAdjustments = __commonJS({
    "Resources/Private/NeosApiUiAdjustments/index.js"() {
      var import_manifest2 = __toESM(require_manifest());
      init_();
    }
  });
  require_NeosApiUiAdjustments();
})();
