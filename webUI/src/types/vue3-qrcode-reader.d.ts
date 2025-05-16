declare module 'vue3-qrcode-reader' {
  import { DefineComponent } from 'vue'

  export const QrcodeStream: DefineComponent<{
    camera?: string
    torch?: boolean
    track?: (location: any, ctx: CanvasRenderingContext2D) => void
  }>

  export const QrcodeCapture: DefineComponent<{
    camera?: string
    torch?: boolean
  }>
} 