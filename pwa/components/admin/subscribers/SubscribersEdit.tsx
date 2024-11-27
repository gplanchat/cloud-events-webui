import {BooleanInput, Edit, PasswordInput, SimpleForm, TextInput} from "react-admin";
import TriggerFilterInput from "./TriggerFilterInput";

const SubscribersEdit = () => (
  <Edit>
    <SimpleForm>
      <TextInput name="label" source="label" />
      <TextInput name="serviceUri" source="serviceUri" />
      <BooleanInput name="verifyPeer" source="verifyPeer" helperText="Disable TLS verification for this subscriber. In case it is using a self-signed certificate you should disable the peer verification." />
      <TriggerFilterInput name="filters" source="filters" />
      <PasswordInput name="bearerAuthentication" source="bearerAuthentication" />
    </SimpleForm>
  </Edit>
)

export default SubscribersEdit
