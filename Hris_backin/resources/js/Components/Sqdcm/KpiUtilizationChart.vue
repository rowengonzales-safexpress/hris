<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const chartContainer = ref(null)
let chart = null

const initChart = () => {
  if (!chartContainer.value) return

  // Clear previous chart
  chartContainer.value.innerHTML = ''

  // Create canvas
  const canvas = document.createElement('canvas')
  canvas.width = 500
  canvas.height = 300
  chartContainer.value.appendChild(canvas)

  const ctx = canvas.getContext('2d')
  const data = props.data

  if (!data || data.length === 0) {
    // Draw "No data available" message
    ctx.fillStyle = '#9CA3AF'
    ctx.font = '16px Arial'
    ctx.textAlign = 'center'
    ctx.fillText('No data available', canvas.width / 2, canvas.height / 2)
    return
  }

  // Chart dimensions and styling
  const padding = 60
  const chartWidth = canvas.width - (padding * 2)
  const chartHeight = canvas.height - (padding * 2)

  // Find max value for scaling (ensure minimum range)
  const maxValue = Math.max(100, Math.max(...data.map(d => d.utilization)))
  const minValue = 0

  // Draw background grid
  ctx.strokeStyle = '#F3F4F6'
  ctx.lineWidth = 1
  
  // Horizontal grid lines
  for (let i = 0; i <= 5; i++) {
    const y = padding + (chartHeight / 5) * i
    ctx.beginPath()
    ctx.moveTo(padding, y)
    ctx.lineTo(padding + chartWidth, y)
    ctx.stroke()
    
    // Y-axis labels
    const value = maxValue - (maxValue / 5) * i
    ctx.fillStyle = '#6B7280'
    ctx.font = '12px Arial'
    ctx.textAlign = 'right'
    ctx.fillText(Math.round(value) + '%', padding - 10, y + 4)
  }

  // Draw axes
  ctx.strokeStyle = '#374151'
  ctx.lineWidth = 2
  ctx.beginPath()
  // Y-axis
  ctx.moveTo(padding, padding)
  ctx.lineTo(padding, padding + chartHeight)
  // X-axis
  ctx.moveTo(padding, padding + chartHeight)
  ctx.lineTo(padding + chartWidth, padding + chartHeight)
  ctx.stroke()

  if (data.length === 1) {
    // Single point - draw as a dot
    const x = padding + chartWidth / 2
    const y = padding + chartHeight - ((data[0].utilization / maxValue) * chartHeight)
    
    ctx.fillStyle = '#10B981'
    ctx.beginPath()
    ctx.arc(x, y, 6, 0, 2 * Math.PI)
    ctx.fill()
    
    // Label
    ctx.fillStyle = '#374151'
    ctx.font = '12px Arial'
    ctx.textAlign = 'center'
    ctx.fillText(data[0].date, x, padding + chartHeight + 20)
    
    return
  }

  // Draw line chart for multiple points
  const stepX = chartWidth / (data.length - 1)
  
  // Draw the line
  ctx.strokeStyle = '#10B981'
  ctx.lineWidth = 3
  ctx.beginPath()
  
  data.forEach((point, index) => {
    const x = padding + (stepX * index)
    const y = padding + chartHeight - ((point.utilization / maxValue) * chartHeight)
    
    if (index === 0) {
      ctx.moveTo(x, y)
    } else {
      ctx.lineTo(x, y)
    }
  })
  ctx.stroke()

  // Draw data points
  data.forEach((point, index) => {
    const x = padding + (stepX * index)
    const y = padding + chartHeight - ((point.utilization / maxValue) * chartHeight)
    
    // Point circle
    ctx.fillStyle = '#10B981'
    ctx.beginPath()
    ctx.arc(x, y, 4, 0, 2 * Math.PI)
    ctx.fill()
    
    // Point border
    ctx.strokeStyle = '#FFFFFF'
    ctx.lineWidth = 2
    ctx.beginPath()
    ctx.arc(x, y, 4, 0, 2 * Math.PI)
    ctx.stroke()
    
    // X-axis labels (dates)
    ctx.fillStyle = '#6B7280'
    ctx.font = '11px Arial'
    ctx.textAlign = 'center'
    const dateLabel = new Date(point.date).toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric' 
    })
    ctx.fillText(dateLabel, x, padding + chartHeight + 20)
    
    // Value labels on hover points
    ctx.fillStyle = '#374151'
    ctx.font = '10px Arial'
    ctx.textAlign = 'center'
    ctx.fillText(Math.round(point.utilization) + '%', x, y - 10)
  })

  // Chart title
  ctx.fillStyle = '#1F2937'
  ctx.font = 'bold 14px Arial'
  ctx.textAlign = 'left'
  ctx.fillText('3 Months Perspective', padding, 25)
}

watch(() => props.data, () => {
  initChart()
}, { deep: true })

onMounted(() => {
  initChart()
})
</script>

<template>
  <div class="w-full">
    <div ref="chartContainer" class="flex justify-center items-center min-h-[300px]"></div>
  </div>
</template>