import { useRecordContext } from 'react-admin';

const CodeTextField = ({ source }: { source: string}) => {
  const record = useRecordContext();

  if (!record) {
    return null
  }

  return (
    <span style={{ fontFamily: 'monospace' }}>{record && record[source]}</span>
  )
}

export default CodeTextField
