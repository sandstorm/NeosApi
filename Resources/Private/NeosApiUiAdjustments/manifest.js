import manifest from '@neos-project/neos-ui-extensibility';
import {wrappedMenuTogglerFactory} from './WrappedMenuToggler';

manifest('Sandstorm.NeosApi', {}, (globalRegistry) => {
	// Registry definitions:
	// https://github.com/neos/neos-ui/blob/9.0/packages/neos-ui/src/manifest.js
	// https://github.com/neos/neos-ui/blob/9.0/packages/neos-ui/src/manifest.containers.js

	const containerRegistry = globalRegistry.get('containers');
	const OriginalMenuToggler = containerRegistry.get('PrimaryToolbar/Left/MenuToggler');
	containerRegistry.set('PrimaryToolbar/Left/MenuToggler', wrappedMenuTogglerFactory(OriginalMenuToggler));
});


