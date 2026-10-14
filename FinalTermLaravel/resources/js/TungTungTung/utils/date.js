export function formatDate(value) {
  const date = new Date(String(value).replace(' ', 'T'))
  return Number.isNaN(date.valueOf()) ? value : date.toLocaleString()
}
