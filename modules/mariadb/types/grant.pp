# @summary an incomplete list of mariadb grants
#   when adding new entries please ensure it's the canonical form used in information_schema
type Mariadb::Grant = Enum[
  'SELECT',
  'INSERT',
  'UPDATE',
  'DELETE',
]
