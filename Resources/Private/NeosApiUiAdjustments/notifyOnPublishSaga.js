import { call, takeEvery } from 'redux-saga/effects';
import {actionTypes} from '@neos-project/neos-ui-redux-store';

const notifyOnPublishWithTargetOrigin = targetOrigin => function*(action) {
	yield call(() => parent.postMessage(action, targetOrigin))
}

export function* notifyOnPublishSaga({ globalRegistry }) {
	const targetOrigin = globalRegistry.get('frontendConfiguration').get('Sandstorm.NeosApi').notifyOnPublishTarget;
	if(targetOrigin) {
		const notifyOnPublish = notifyOnPublishWithTargetOrigin(targetOrigin);
		yield takeEvery(actionTypes.CR.Publishing.FINISHED, notifyOnPublish)
	}
}
