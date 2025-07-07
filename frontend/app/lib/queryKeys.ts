export const queryKeys = {
  files: {
    all: ['files'] as const,
    lists: () => [...queryKeys.files.all, 'list'] as const,
    list: (filters: Record<string, any>) => [...queryKeys.files.lists(), { filters }] as const,
    details: () => [...queryKeys.files.all, 'detail'] as const,
    detail: (id: number) => [...queryKeys.files.details(), id] as const,
  },
} as const;