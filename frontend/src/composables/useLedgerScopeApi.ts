import * as endpointModule from "@/api/endpoints";

/**
 * Domain pages consume API capabilities through this composable instead of
 * importing the transport module directly. Keeping this boundary in one
 * place makes it straightforward to replace an endpoint with a store-backed
 * implementation without changing page contracts.
 */
export function useLedgerScopeApi(): typeof endpointModule {
  return endpointModule;
}
