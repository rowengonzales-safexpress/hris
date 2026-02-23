<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  }
})

const chartContainer = ref(null)

const initChart = () => {
  if (!chartContainer.value) return

  // Clear previous chart
  chartContainer.value.innerHTML = ''

  const data = props.data || []

  if (data.length === 0) {
    const noDataDiv = document.createElement('div')
    noDataDiv.className = 'text-gray-500 text-sm text-center py-8'
    noDataDiv.textContent = 'No data available'
    chartContainer.value.appendChild(noDataDiv)
    return
  }

  // Create canvas
  const canvas = document.createElement('canvas')
  canvas.width = 600
  canvas.height = 350
  canvas.className = 'w-full h-auto'
  chartContainer.value.appendChild(canvas)

  const ctx = canvas.getContext('2d')

  // Chart dimensions
  const padding = 80
  const chartWidth = canvas.width - (padding * 2)
  const chartHeight = canvas.height - (padding * 2)

  // Chart title
  ctx.fillStyle = '#374151'
  ctx.font = 'bold 16px Arial'
  ctx.textAlign = 'left'
  ctx.fillText('KPI Standing', padding, 30)
  ctx.font = '12px Arial'
  ctx.fillStyle = '#6B7280'
  ctx.fillText('HIT vs MISS Per Week', padding, 50)

  // Find max value for scaling
  const maxValue = Math.max(...data.map(d => Math.max(d.hit || 0, d.miss || 0)))
  const scaledMax = Math.ceil(maxValue * 1.1) // Add 10% padding

  // Bar dimensions
  const barGroupWidth = chartWidth / data.length * 0.8
  const barWidth = barGroupWidth / 2 * 0.8
  const barSpacing = chartWidth / data.length * 0.2

  // Draw grid lines
  ctx.strokeStyle = '#F3F4F6'
  ctx.lineWidth = 0.5
  
  // Horizontal grid lines
  for (let i = 0; i <= 5; i++) {
    const y = padding + (chartHeight / 5) * i
    ctx.beginPath()
    ctx.moveTo(padding, y)
    ctx.lineTo(padding + chartWidth, y)
    ctx.stroke()
    
    // Y-axis labels
    const value = scaledMax - (scaledMax / 5) * i
    ctx.fillStyle = '#6B7280'
    ctx.font = '12px Arial'
    ctx.textAlign = 'right'
    ctx.fillText(Math.round(value), padding - 10, y + 4)
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

  // Draw bars
  data.forEach((point, index) => {
    const x = padding + (chartWidth / data.length) * index + barSpacing / 2
    
    // HIT bar (green)
    const hitHeight = ((point.hit || 0) / scaledMax) * chartHeight
    const hitY = padding + chartHeight - hitHeight
    
    ctx.fillStyle = '#10B981'
    ctx.fillRect(x, hitY, barWidth, hitHeight)
    
    // MISS bar (red)
    const missHeight = ((point.miss || 0) / scaledMax) * chartHeight
    const missY = padding + chartHeight - missHeight
    
    ctx.fillStyle = '#EF4444'
    ctx.fillRect(x + barWidth + 5, missY, barWidth, missHeight)
    
    // Category labels
    ctx.fillStyle = '#374151'
    ctx.font = '12px Arial'
    ctx.textAlign = 'center'
    const labelX = x + barWidth + 2.5
    ctx.fillText(point.category || `W${index + 1}`, labelX, padding + chartHeight + 20)
    
    // Value labels on bars
    ctx.fillStyle = '#FFFFFF'
    ctx.font = 'bold 10px Arial'
    ctx.textAlign = 'center'
    
    if (hitHeight > 20) {
      ctx.fillText(point.hit || 0, x + barWidth / 2, hitY + hitHeight / 2 + 3)
    }
    
    if (missHeight > 20) {
      ctx.fillText(point.miss || 0, x + barWidth + 5 + barWidth / 2, missY + missHeight / 2 + 3)
    }
  })

  // Legend
  const legendY = padding + chartHeight + 45
  
  // HIT legend
  ctx.fillStyle = '#10B981'
  ctx.fillRect(padding, legendY, 15, 15)
  ctx.fillStyle = '#374151'
  ctx.font = '12px Arial'
  ctx.textAlign = 'left'
  ctx.fillText('HIT', padding + 20, legendY + 12)
  
  // MISS legend
  ctx.fillStyle = '#EF4444'
  ctx.fillRect(padding + 80, legendY, 15, 15)
  ctx.fillStyle = '#374151'
  ctx.fillText('MISS', padding + 105, legendY + 12)
}

onMounted(() => {
  initChart()
})

watch(() => props.data, () => {
  initChart()
}, { deep: true })
</script>

<template>
  <div class="w-full">
    <div ref="chartContainer" class="flex justify-center items-center min-h-[350px]"></div>
  </div>
</template>