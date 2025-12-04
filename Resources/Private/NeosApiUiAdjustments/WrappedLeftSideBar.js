import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';

export function wrappedLeftSideBarFactory(OriginalLeftSideBar) {
	class WrappedLeftSideBar extends PureComponent {
		render() {
			const {showLeftSideBar} = this.props;

			if (showLeftSideBar)  {
				return <OriginalLeftSideBar />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showLeftSideBar: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showLeftSideBar
	}))(WrappedLeftSideBar);
}

