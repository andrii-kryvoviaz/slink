export type SelectionState = 'checked' | 'unchecked' | 'partial';

export class UserIntent {
  static readonly Add = new UserIntent('checked');
  static readonly Remove = new UserIntent('unchecked');
  static readonly Unchanged = new UserIntent(undefined);

  readonly isActive: boolean;

  private constructor(readonly selectionState: SelectionState | undefined) {
    this.isActive = selectionState !== undefined;
  }
}

export interface AssignmentContext {
  counts: Map<string, number>;
  total: number;
}

export interface PendingSelection {
  isPending(id: string): boolean;
  reset(): void;
}

function resolveSelectionState(
  assignedCount: number,
  totalItems: number,
): SelectionState {
  if (assignedCount === 0) return 'unchecked';
  if (assignedCount === totalItems) return 'checked';
  return 'partial';
}

class IntentCycle {
  private static readonly registry = new Map<SelectionState, IntentCycle>([
    [
      'checked',
      new IntentCycle([
        [UserIntent.Unchanged, UserIntent.Remove],
        [UserIntent.Remove, UserIntent.Unchanged],
      ]),
    ],
    [
      'partial',
      new IntentCycle([
        [UserIntent.Unchanged, UserIntent.Add],
        [UserIntent.Add, UserIntent.Remove],
        [UserIntent.Remove, UserIntent.Unchanged],
      ]),
    ],
    [
      'unchecked',
      new IntentCycle([
        [UserIntent.Unchanged, UserIntent.Add],
        [UserIntent.Add, UserIntent.Unchanged],
      ]),
    ],
  ]);

  private _transitions: Map<UserIntent, UserIntent>;

  constructor(cycle: [UserIntent, UserIntent][]) {
    this._transitions = new Map(cycle);
  }

  next(current: UserIntent): UserIntent {
    return this._transitions.get(current) ?? current;
  }

  static forState(state: SelectionState): IntentCycle {
    return IntentCycle.registry.get(state)!;
  }
}

export class PendingMultiSelection implements PendingSelection {
  private _context: () => AssignmentContext;
  private _intents: Map<string, UserIntent> = $state(new Map());

  readonly addedIds: string[] = $derived.by(() =>
    this.filterByIntent(UserIntent.Add),
  );
  readonly removedIds: string[] = $derived.by(() =>
    this.filterByIntent(UserIntent.Remove),
  );
  readonly changeCount: number = $derived(
    [...this._intents.values()].filter((i) => i.isActive).length,
  );
  readonly hasChanges: boolean = $derived(this.changeCount > 0);

  constructor(context: () => AssignmentContext) {
    this._context = context;
  }

  private filterByIntent(intent: UserIntent): string[] {
    return [...this._intents.entries()]
      .filter(([, i]) => i === intent)
      .map(([id]) => id);
  }

  getOriginalState(id: string): SelectionState {
    const { counts, total } = this._context();
    return resolveSelectionState(counts.get(id) ?? 0, total);
  }

  getState(id: string): SelectionState {
    const intent = this._intents.get(id) ?? UserIntent.Unchanged;
    return intent.selectionState ?? this.getOriginalState(id);
  }

  isPending(id: string): boolean {
    return this._intents.get(id)?.isActive ?? false;
  }

  toggle(id: string): void {
    const original = this.getOriginalState(id);
    const current = this._intents.get(id) ?? UserIntent.Unchanged;
    const next = IntentCycle.forState(original).next(current);

    const updated = new Map(this._intents);
    updated.set(id, next);
    this._intents = updated;
  }

  reset(): void {
    this._intents = new Map();
  }
}

export class PendingSingleSelection implements PendingSelection {
  private _pending: string | null = $state(null);

  get pending(): string | null {
    return this._pending;
  }

  get hasPending(): boolean {
    return this._pending !== null;
  }

  isPending(id: string): boolean {
    return this._pending === id;
  }

  select(id: string): void {
    this._pending = this._pending === id ? null : id;
  }

  reset(): void {
    this._pending = null;
  }
}

export const createPendingMultiSelection = (context: () => AssignmentContext) =>
  new PendingMultiSelection(context);

export const createPendingSingleSelection = () => new PendingSingleSelection();
