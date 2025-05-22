/**
 * تبدیل timestamp به تاریخ شمسی
 * @param {number|string} timestamp - timestamp مورد نظر
 * @returns {string} - تاریخ شمسی
 */
export const formatDate = (timestamp) => {
  if (!timestamp) return ''
  
  try {
    // اگر timestamp به صورت رشته است، آن را به عدد تبدیل می‌کنیم
    const ts = typeof timestamp === 'string' ? parseInt(timestamp) : timestamp
    
    // اگر timestamp به ثانیه است، آن را به میلی‌ثانیه تبدیل می‌کنیم
    const date = new Date(ts * 1000)
    
    // بررسی معتبر بودن تاریخ
    if (isNaN(date.getTime())) {
      console.warn('Invalid date:', timestamp)
      return ''
    }
    
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      calendar: 'persian'
    }
    
    return new Intl.DateTimeFormat('fa-IR', options).format(date)
  } catch (error) {
    console.error('Error formatting date:', error)
    return ''
  }
}

/**
 * تبدیل timestamp به تاریخ و زمان شمسی
 * @param {number|string} timestamp - timestamp مورد نظر
 * @returns {string} - تاریخ و زمان شمسی
 */
export const formatDateTime = (timestamp) => {
  if (!timestamp) return ''
  
  try {
    // اگر timestamp به صورت رشته است، آن را به عدد تبدیل می‌کنیم
    const ts = typeof timestamp === 'string' ? parseInt(timestamp) : timestamp
    
    // اگر timestamp به ثانیه است، آن را به میلی‌ثانیه تبدیل می‌کنیم
    const date = new Date(ts * 1000)
    
    // بررسی معتبر بودن تاریخ
    if (isNaN(date.getTime())) {
      console.warn('Invalid date:', timestamp)
      return ''
    }
    
    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      calendar: 'persian'
    }
    
    return new Intl.DateTimeFormat('fa-IR', options).format(date)
  } catch (error) {
    console.error('Error formatting date:', error)
    return ''
  }
} 