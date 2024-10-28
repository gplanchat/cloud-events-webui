import {useRecordContext} from "react-admin";
import JsonView from "@uiw/react-json-view";

const JsonField = ({ source }: { source: string}) => {
  const record = useRecordContext();

  if (!record) {
    return null
  }

  return (
    <JsonView value={record[source]} />
  );
}

export default JsonField
