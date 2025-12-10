// @ts-ignore
import React, { PureComponent } from 'react';
// @ts-ignore
import { neos } from '@neos-project/neos-ui-decorators';
import {NeosApiSettings} from "./settings";

export default function wrappedComponentFactory(selector: (settings: NeosApiSettings) => boolean) {
	return function(OriginalComponent: React.Component) {
		class WrappedComponent extends PureComponent {
			render() {
				const {showComponent} = this.props;

				if (showComponent)  {
					return <OriginalComponent />;
				}
				return null;
			}
		}

		return neos(globalRegistry => ({
			showComponent: selector(globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi'))
		}))(WrappedComponent);
	}
}
