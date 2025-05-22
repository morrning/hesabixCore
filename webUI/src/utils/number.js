/**
 * فرمت‌بندی اعداد به فرمت فارسی
 * @param {number} number - عدد مورد نظر
 * @returns {string} - عدد فرمت شده
 */
export const formatNumber = (number) => {
  if (!number) return '0'
  return new Intl.NumberFormat('fa-IR').format(number)
}

/**
 * تبدیل عدد به فرمت پول
 * @param {number} number - عدد مورد نظر
 * @param {string} currency - واحد پول
 * @returns {string} - مبلغ فرمت شده
 */
export const formatCurrency = (number, currency = 'ریال') => {
  return `${formatNumber(number)} ${currency}`
} 