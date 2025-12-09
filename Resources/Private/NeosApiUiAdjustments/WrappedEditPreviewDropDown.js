import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';

export function wrappedEditPreviewDropDownFactory(OriginalEditPreviewDropDown) {
	class WrappedEditPreviewDropDown extends PureComponent {
		render() {
			const {showEditPreviewDropDown} = this.props;

			if (showEditPreviewDropDown)  {
				return <OriginalEditPreviewDropDown />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showEditPreviewDropDown: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showEditPreviewDropDown
	}))(WrappedEditPreviewDropDown);
}

