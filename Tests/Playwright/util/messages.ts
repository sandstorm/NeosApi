import {Page} from "@playwright/test";

type MessageReceiver = {
  all: ReceivedEvent[],
  first: Promise<ReceivedEvent>
}

type ReceivedEvent = {
  data: MessageEvent["data"],
  lastEventId: MessageEvent["lastEventId"],
  origin: MessageEvent["origin"],
}

export async function receiveMessages(
  page: Page,
  filter: (message: ReceivedEvent) => Boolean
): Promise<MessageReceiver> {
  const messages: ReceivedEvent[] = [];
  let deferredResolve: (event: ReceivedEvent) => void;
  const firstMessage = new Promise<ReceivedEvent>(resolve => deferredResolve = resolve);

  const name = "playwrightPublishMessage" + Math.random() + "At" + Date.now()
  await page.exposeFunction(name, (event: ReceivedEvent) => {
    if(!filter || filter(event)) {
      if(messages.length == 0) {
        deferredResolve(event);
      }
      messages.push(event);
    }
  });

  await page.addInitScript(({ functionName }) =>
    window.addEventListener('message', (event: MessageEvent) => {
      window[functionName]({ data: event.data, lastEventId: event.lastEventId, origin: event.origin });
    }),
    { functionName: name }
  );

  return {
    all: messages,
    first: firstMessage
  };
}
