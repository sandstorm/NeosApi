import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';

export function wrappedPublishDropDownFactory(OriginalPublishDropDown) {
	class wrappedPublishDropDown extends PureComponent {
		render() {
			const {showPublishDropDown} = this.props;

			if (showPublishDropDown)  {
				return <OriginalPublishDropDown />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showPublishDropDown: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showPublishDropDown
	}))(wrappedPublishDropDown);
}

