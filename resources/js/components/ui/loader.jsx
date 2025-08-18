import * as React from 'react'

// Reusable spinner that looks like a broken (dashed) circle, so rotation is visible
// Usage: <Loader className="size-4 text-current" />
export function Loader({ className = 'size-4 text-current', thickness = 4, gap = 16, radius = 10, ...props }) {
  // For viewBox 0 0 24 24 with r=10, circumference ~ 62.83
  // dashLength = circumference - gap, leaving a visible gap segment
  const circumference = 2 * Math.PI * radius
  const dashLength = Math.max(0, circumference - gap)

  return (
    <svg
      className={["animate-spin", className].filter(Boolean).join(' ')}
      viewBox="0 0 24 24"
      fill="none"
      aria-hidden="true"
      {...props}
    >
      <circle
        cx="12"
        cy="12"
        r={radius}
        stroke="currentColor"
        strokeWidth={thickness}
        strokeLinecap="round"
        fill="none"
        // Create a broken circle with a gap so the rotation is obvious
        strokeDasharray={`${dashLength} ${gap}`}
      />
    </svg>
  )
}

export default Loader
