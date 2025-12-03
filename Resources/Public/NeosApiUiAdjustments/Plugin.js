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

  // Resources/Private/NeosApiUiAdjustments/manifest.js
  var require_manifest = __commonJS({
    "Resources/Private/NeosApiUiAdjustments/manifest.js"() {
      init_dist();
      init_WrappedMenuToggler();
      dist_default("Sandstorm.NeosApi", {}, (globalRegistry) => {
        console.log("HALLO WELT");
        console.log(globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi"));
        window.setTimeout(() => {
          console.log(globalRegistry.get("frontendConfiguration").get("Sandstorm.NeosApi"));
        }, 100);
        const containerRegistry = globalRegistry.get("containers");
        const OriginalMenuToggler = containerRegistry.get("PrimaryToolbar/Left/MenuToggler");
        containerRegistry.set("PrimaryToolbar/Left/MenuToggler", wrappedMenuTogglerFactory(OriginalMenuToggler));
      });
    }
  });

  // Resources/Private/NeosApiUiAdjustments/index.css
  var init_ = __esm({
    "Resources/Private/NeosApiUiAdjustments/index.css"() {
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
