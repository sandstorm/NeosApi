import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';

export function wrappedMenuTogglerFactory(OriginalMenuToggler) {
	class WrappedMenuToggler extends PureComponent {
		render() {
			const {showMainMenu} = this.props;

			if (showMainMenu)  {
				return <OriginalMenuToggler />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showMainMenu: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showMainMenu
	}))(WrappedMenuToggler);
}

