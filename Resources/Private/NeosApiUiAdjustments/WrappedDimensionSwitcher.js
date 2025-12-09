import React, { PureComponent } from 'react';
import { neos } from '@neos-project/neos-ui-decorators';

export function wrappedDimensionSwitcherFactory(OriginalDimensionSwitcher) {
	class wrappedDimensionSwitcher extends PureComponent {
		render() {
			const {showDimensionSwitcher} = this.props;

			if (showDimensionSwitcher)  {
				return <OriginalDimensionSwitcher />;
			}
			return null;
		}
	}

	return neos(globalRegistry => ({
		showDimensionSwitcher: globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').showDimensionSwitcher
	}))(wrappedDimensionSwitcher);
}

