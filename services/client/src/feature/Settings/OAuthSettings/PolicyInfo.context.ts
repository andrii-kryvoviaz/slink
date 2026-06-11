import { getContext, setContext } from 'svelte';

export interface PolicyInfoContext {
  readonly value: string;
}

const POLICY_INFO_KEY = Symbol('policy-info');

export const setPolicyInfoContext = (context: PolicyInfoContext): void => {
  setContext(POLICY_INFO_KEY, context);
};

export const getPolicyInfoContext = (): PolicyInfoContext => {
  return getContext(POLICY_INFO_KEY);
};
