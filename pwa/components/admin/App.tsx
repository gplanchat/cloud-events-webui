import {HydraAdmin, ResourceGuesser} from "@api-platform/admin";
import EventsList from "./events/EventsList";
import EventsShow from "./events/EventsShow";
import SubscribersEdit from "./subscribers/SubscribersEdit";
import SubscriberCreate from "./subscribers/SubscriberCreate";
import SubscribersShow from "./subscribers/SubscribersShow";
import SubscribersList from "./subscribers/SubscribersList";

const App = () => (
  <HydraAdmin
    entrypoint={window.origin}
    title="API Platform admin"
  >
    <ResourceGuesser name="events" list={EventsList} show={EventsShow} />
    <ResourceGuesser name="subscribers" list={SubscribersList} edit={SubscribersEdit} create={SubscriberCreate} show={SubscribersShow} />
  </HydraAdmin>
);

export default App;
