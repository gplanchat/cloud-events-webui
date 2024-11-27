import {Datagrid, EditButton, List, ShowButton, TextField} from "react-admin";

const SubscribersList = () => (
  <List>
    <Datagrid>
      <TextField source="label" />
      <TextField source="serviceUri" />
      <EditButton />
      <ShowButton />
    </Datagrid>
  </List>
)

export default SubscribersList
