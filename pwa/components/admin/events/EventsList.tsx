import {
  Datagrid,
  DateField,
  ExportButton,
  FilterButton,
  List,
  SearchInput,
  SelectColumnsButton,
  TextField,
  TextInput,
  TopToolbar
} from "react-admin";

const ListActions = () => (
  <TopToolbar>
    <SelectColumnsButton />
    <FilterButton/>
    <ExportButton/>
  </TopToolbar>
)

const filters = [
  <SearchInput key="free_text" name="free_text" source="q" alwaysOn />,
  <TextInput key="source" label="Source" name="source" source="source" />,
]

const EventsList = () => {
  return (
    <List filters={filters} actions={<ListActions />}>
      <Datagrid>
        <TextField source="eventId" />
        <TextField source="specVersion" />
        <TextField source="type" />
        <TextField source="source" />
        <DateField source="time" />
      </Datagrid>
    </List>
  )
}

export default EventsList
