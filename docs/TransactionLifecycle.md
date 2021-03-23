# Transaction Lifecycle

Class | Parent | Description
--- | --- | ---
`AbstractTx` | --- | Parent class of all transaction related classes
`AbstractTxConstructor` | `AbstractTx` | API for constructing transactions that accepts arguments as methods
`AbstractPreparedTx` | `AbstractTx` | Decodes a prepared/encoded transaction
`Transaction` | `AbstractPreparedTx` | Non-abstract general purpose prepared transaction class
