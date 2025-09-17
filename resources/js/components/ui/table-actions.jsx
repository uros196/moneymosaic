import { Link } from '@inertiajs/react'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { Eye, Pencil, Trash2 } from 'lucide-react'
import { useI18n } from '@/i18n'
import { useConfirmDelete } from '@/components/ui/confirm-delete-provider'

export function ViewAction({ href, label, prefetch = true }) {
  const { __ } = useI18n()
  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <Link href={href} prefetch={prefetch} className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent">
          <Eye className="size-4" />
        </Link>
      </TooltipTrigger>
      <TooltipContent>{label || __('common.view')}</TooltipContent>
    </Tooltip>
  )
}

export function EditAction({ onClick, label }) {
  const { __ } = useI18n()
  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <button className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent" onClick={onClick}>
          <Pencil className="size-4" />
        </button>
      </TooltipTrigger>
      <TooltipContent>{label || __('common.edit')}</TooltipContent>
    </Tooltip>
  )
}

export function DeleteAction({
  onConfirm,
  label,
  confirmTitle,
  confirmDescription,
  confirmText,
  cancelText,
  requirePassword = false,
  verifyPasswordUrl,
}) {
  const { __ } = useI18n()
  const { openConfirmDelete } = useConfirmDelete()

  const handleClick = () => {
    openConfirmDelete({
      title: confirmTitle,
      description: confirmDescription,
      confirmText,
      cancelText,
      requirePassword,
      verifyPasswordUrl,
      onConfirm,
    })
  }

  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <button className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent" onClick={handleClick}>
          <Trash2 className="size-4 text-destructive" />
        </button>
      </TooltipTrigger>
      <TooltipContent>{label || __('common.delete')}</TooltipContent>
    </Tooltip>
  )
}
