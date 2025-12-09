import manifest from '@neos-project/neos-ui-extensibility';
import {wrappedMenuTogglerFactory} from './WrappedMenuToggler';
import {wrappedLeftSideBarFactory} from './WrappedLeftSideBar';
import {wrappedEditPreviewDropDownFactory} from './WrappedEditPreviewDropDown'

manifest('Sandstorm.NeosApi', {}, (globalRegistry) => {
	// Registry definitions:
	// https://github.com/neos/neos-ui/blob/9.0/packages/neos-ui/src/manifest.js
	// https://github.com/neos/neos-ui/blob/9.0/packages/neos-ui/src/manifest.containers.js

	const containerRegistry = globalRegistry.get('containers');
	const wrapContainer = (name, wrapperFactory) => {
		const OriginalContainer = containerRegistry.get(name);
		containerRegistry.set(name, wrapperFactory(OriginalContainer));
	}

	wrapContainer('PrimaryToolbar/Left/MenuToggler', wrappedMenuTogglerFactory);
	wrapContainer('LeftSideBar', wrappedLeftSideBarFactory);
	wrapContainer('PrimaryToolbar/Right/EditPreviewDropDown', wrappedEditPreviewDropDownFactory);
});


