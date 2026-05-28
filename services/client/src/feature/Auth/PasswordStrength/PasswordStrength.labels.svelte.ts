class StrengthLabelsState {
  get weak() {
    return 'Weak';
  }
  get fair() {
    return 'Fair';
  }
  get good() {
    return 'Good';
  }
  get strong() {
    return 'Strong';
  }
  get veryStrong() {
    return 'Very Strong';
  }
}

export type StrengthLevel = keyof StrengthLabelsState;

export const strengthLabels = new StrengthLabelsState();
