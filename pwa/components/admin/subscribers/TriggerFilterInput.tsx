import {
  ArrayInput,
  CommonInputProps,
  ResettableTextFieldProps,
  SelectInput,
  SimpleFormIterator,
  TextInput,
} from "react-admin";

declare type TriggerFilterInputProps = CommonInputProps & ResettableTextFieldProps

const TriggerFilterInput = (props: TriggerFilterInputProps) => {
  const { source, name, ...rest } = props;

  return (
    <ArrayInput source={source}>
      <SimpleFormIterator inline>
        <SelectInput source="type" choices={[
          { id: 'exact', name: 'Exact' },
          { id: 'prefix', name: 'Prefix' },
          { id: 'suffix', name: 'Suffix' },
        ]} />
        <TextInput source="value"/>
      </SimpleFormIterator>
    </ArrayInput>
  );
}

export default TriggerFilterInput
