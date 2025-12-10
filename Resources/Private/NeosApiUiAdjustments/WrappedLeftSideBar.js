import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';
import { LeftSideBarWithoutDocumentTree } from './LeftSideBarWithoutDocumentTree'

export function wrappedLeftSideBarFactory(OriginalLeftSideBar) {
	class WrappedLeftSideBar extends PureComponent {
		render() {
			const {showLeftSideBar, showDocumentTree} = this.props;

			if (showLeftSideBar && showDocumentTree)  {
				return <OriginalLeftSideBar />;
			} else if(showLeftSideBar && !showDocumentTree) {
				return <LeftSideBarWithoutDocumentTree />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showLeftSideBar: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showLeftSideBar,
		showDocumentTree: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showDocumentTree
	}))(WrappedLeftSideBar);
}

