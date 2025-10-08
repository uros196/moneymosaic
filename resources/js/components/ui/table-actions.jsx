/**
 * Components for consistent table actions (menu and buttons).
 * Provides View, Edit, and Delete variants, with options for tooltips and display inside a dropdown menu.
 * No state changes; DeleteAction only opens the global ConfirmDelete dialog.
 */
import { Link } from '@inertiajs/react'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { Eye, Pencil, Trash2, MoreVertical } from 'lucide-react'
import { useI18n } from '@/i18n'
import { useConfirmDelete } from '@/components/ui/confirm-delete-provider'

/**
 * TableActionsMenu
 * - Kebab menu (three dots) that shows the provided actions.
 * Props:
 * - label: aria-label for the menu button (defaults to 'Actions')
 * - children: <DropdownMenuItem> items (e.g., ViewAction/EditAction/DeleteAction with inMenu={true})
 */
export function TableActionsMenu({ label = 'Actions', children }) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button type="button" variant="ghost" size="icon" aria-label={label}>
          <MoreVertical className="size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">{children}</DropdownMenuContent>
    </DropdownMenu>
  )
}

/**
 * ViewAction
 * - "View" action that renders an Inertia Link.
 * Props:
 * - href: URL the link points to
 * - label: text for tooltip/label (default __('common.view'))
 * - prefetch: whether Inertia should prefetch the route
 * - tooltip: render as an icon with tooltip (true) or icon + text without tooltip (false)
 * - inMenu: render as a DropdownMenuItem instead of a standalone button
 */
export function ViewAction({ href, label, prefetch = true, tooltip = true, inMenu = false }) {
  const { __ } = useI18n()
  const resolvedLabel = label || __('common.view')

  if (inMenu) {
    return (
      <DropdownMenuItem asChild>
        <Link href={href} prefetch={prefetch}>
          <Eye className="size-4" />
          <span>{resolvedLabel}</span>
        </Link>
      </DropdownMenuItem>
    )
  }

  if (tooltip) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>
          <Link href={href} prefetch={prefetch} className="inline-flex cursor-pointer items-center rounded-md border px-2 py-1 hover:bg-accent">
            <Eye className="size-4" />
          </Link>
        </TooltipTrigger>
        <TooltipContent>{resolvedLabel}</TooltipContent>
      </Tooltip>
    )
  }

  return (
    <Link href={href} prefetch={prefetch} className="inline-flex cursor-pointer items-center gap-2 rounded-md border px-2 py-1 hover:bg-accent">
      <Eye className="size-4" />
      <span>{resolvedLabel}</span>
    </Link>
  )
}

/**
 * EditAction
 * - "Edit" action that triggers the onClick handler.
 * Props:
 * - onClick: callback that opens the edit form/drawer
 * - label: text for tooltip/label (default __('common.edit'))
 * - tooltip: render as an icon with tooltip (true) or icon + text without tooltip (false)
 * - inMenu: render as a DropdownMenuItem instead of a standalone button
 */
export function EditAction({ onClick, label, tooltip = true, inMenu = false }) {
  const { __ } = useI18n()
  const resolvedLabel = label || __('common.edit')

  if (inMenu) {
    return (
      <DropdownMenuItem
        onSelect={(e) => {
          e.preventDefault()
          onClick?.()
        }}
      >
        <Pencil className="size-4" />
        <span>{resolvedLabel}</span>
      </DropdownMenuItem>
    )
  }

  if (tooltip) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>
          <button className="inline-flex cursor-pointer items-center rounded-md border px-2 py-1 hover:bg-accent" onClick={onClick}>
            <Pencil className="size-4" />
          </button>
        </TooltipTrigger>
        <TooltipContent>{resolvedLabel}</TooltipContent>
      </Tooltip>
    )
  }

  return (
    <button className="inline-flex cursor-pointer items-center gap-2 rounded-md border px-2 py-1 hover:bg-accent" onClick={onClick}>
      <Pencil className="size-4" />
      <span>{resolvedLabel}</span>
    </button>
  )
}

/**
 * DeleteAction
 * - "Delete" action that opens the global confirmation modal.
 * Props:
 * - onConfirm: callback executed after confirmation (and password validation if enabled)
 * - label: text for tooltip/label (default __('common.delete'))
 * - confirmTitle, confirmDescription, confirmText, cancelText: texts used in the confirmation modal
 * - requirePassword: whether to require password entry on confirm
 * - verifyPasswordUrl: route used to verify the password
 * - tooltip: render as an icon with tooltip (true) or icon + text without tooltip (false)
 * - inMenu: render as a DropdownMenuItem instead of a standalone button
 */
export function DeleteAction({
  onConfirm,
  label,
  confirmTitle,
  confirmDescription,
  confirmText,
  cancelText,
  requirePassword = false,
  verifyPasswordUrl,
  tooltip = true,
  inMenu = false,
}) {
  const { __ } = useI18n()
  const { openConfirmDelete } = useConfirmDelete()
  const resolvedLabel = label || __('common.delete')

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

  if (inMenu) {
    return (
      <DropdownMenuItem
        variant="destructive"
        onSelect={(e) => {
          e.preventDefault()
          handleClick()
        }}
      >
        <Trash2 className="size-4 text-destructive" />
        <span>{resolvedLabel}</span>
      </DropdownMenuItem>
    )
  }

  if (tooltip) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>
          <button className="inline-flex cursor-pointer items-center rounded-md border px-2 py-1 hover:bg-accent" onClick={handleClick}>
            <Trash2 className="size-4 text-destructive" />
          </button>
        </TooltipTrigger>
        <TooltipContent>{resolvedLabel}</TooltipContent>
      </Tooltip>
    )
  }

  return (
    <button className="inline-flex cursor-pointer items-center gap-2 rounded-md border px-2 py-1 hover:bg-accent" onClick={handleClick}>
      <Trash2 className="size-4 text-destructive" />
      <span>{resolvedLabel}</span>
    </button>
  )
}
