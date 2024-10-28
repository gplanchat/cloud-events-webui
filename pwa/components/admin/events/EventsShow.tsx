import {ArrayField, DateField, Show, SimpleShowLayout, UrlField} from "react-admin";
import JsonField from "./JsonField";
import CodeTextField from "./CodeTextField";

const EventsShow = () => {
  return (
    <Show>
      <SimpleShowLayout>
        <CodeTextField source="eventId" />
        <CodeTextField source="specVersion" />
        <CodeTextField source="type" />
        <CodeTextField source="source" />
        <DateField source="time" />
        <CodeTextField source="dataContentType" />
        <UrlField source="dataSchema" />
        <CodeTextField source="subject" />
        <ArrayField source="extensions">
          <CodeTextField source="" />
        </ArrayField>
        <JsonField source="data" />
      </SimpleShowLayout>
    </Show>
  )
}

export default EventsShow
