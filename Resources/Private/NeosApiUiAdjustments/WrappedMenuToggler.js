import React, { PureComponent } from 'react';
//import PropTypes from 'prop-types';
import { neos } from '@neos-project/neos-ui-decorators';
//import { selectors } from '@neos-project/neos-ui-redux-store';
//import { connect } from 'react-redux';
//import { Button } from '@neos-project/react-ui-components';
//import FullscreenReferencesMultiselect from './FullscreenReferencesMultiselect';


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

