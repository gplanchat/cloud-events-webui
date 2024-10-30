import {
  ArrayField,
  BooleanField,
  Datagrid,
  SelectField,
  Show,
  SimpleShowLayout,
  TextField,
  UrlField
} from "react-admin";
import HttpsIcon from '@mui/icons-material/Https'
import NoEncryptionIcon from '@mui/icons-material/NoEncryption'

const SubscribersShow = () => {
  return (
    <Show>
      <SimpleShowLayout>
        <UrlField source="serviceUri" />
        <BooleanField
          source="verifyPeer"
          valueLabelTrue="Verifies TLS certificate"
          valueLabelFalse="Does not verify TLS certificate"
          TrueIcon={HttpsIcon}
          FalseIcon={NoEncryptionIcon}
        />
        <ArrayField source="filters">
          <Datagrid>
            <SelectField source="type" choices={[
              { id: 'exact', name: 'Exact' },
              { id: 'prefix', name: 'Prefix' },
              { id: 'suffix', name: 'Suffix' },
            ]} />
            <TextField source="value" />
          </Datagrid>
        </ArrayField>
      </SimpleShowLayout>
    </Show>
  )
}

export default SubscribersShow
