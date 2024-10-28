import {HydraAdmin, ResourceGuesser} from "@api-platform/admin";
import EventsList from "./events/EventsList";
import EventsShow from "./events/EventsShow";

const App = () => (
  <HydraAdmin
    entrypoint={window.origin}
    title="API Platform admin"
  >
    <ResourceGuesser name="events" list={EventsList} show={EventsShow} />
  </HydraAdmin>
);

export default App;
