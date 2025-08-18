import { cn } from '@/lib/utils';

export function Icon({ iconNode: IconComponent, className = '', ...props }) {
  if (!IconComponent) {
    return null;
  }
  return <IconComponent className={cn('h-4 w-4', className)} {...props} />;
}
