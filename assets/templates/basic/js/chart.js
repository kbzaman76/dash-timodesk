// ==========================  Chart Js Start ==============

// render bar chart
function overviewCardChart({ elementId, data, colors }) {
  const chartElement = document.getElementById(elementId);
  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const option = {
    grid: {
      left: 0,
      right: 0,
      top: 0,
      bottom: 0,
    },
    xAxis: {
      type: "category",
      boundaryGap: false,
      axisLine: {
        show: false,
      },
      axisLabel: {
        show: false,
      },
      axisTick: {
        show: false,
      },
    },
    yAxis: {
      type: "value",
      show: false,
    },
    series: [
      {
        data: data,
        type: "line",
        smooth: true,
        symbol: "none",
        areaStyle: {
          color: colors[0] + "40",
        },
        lineStyle: {
          color: colors[0],
          width: 2,
        },
      },
    ],
  };

  chartInstance.setOption(option);
  window.addEventListener("resize", () => chartInstance.resize());
}

function renderBarChart({
  elementId,
  data = [],
  colors = [],
  xAxisData = [],
  isTime = false,
  unitLabel,
  showLabels = true,
  rotateLabel = false,
  element = null,
  showTooltip = false,
  sliceX = false,
  showYaxis = true,
}) {
  const chartElement = element ? element : document.getElementById(elementId);

  const fullXAxisData = [...xAxisData];

  if (sliceX) {
    xAxisData = xAxisData.map((d) => d.slice(0, 7) + "...");
  }

  let existingInstance = echarts.getInstanceByDom(chartElement);
  if (existingInstance) {
    existingInstance.dispose();
  }

  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  data = (data || []).map((v) => Number(v) || 0);

  const maxValue = Number(data.length ? Math.max(...data) : 0);
  const bgMax = getSmartMax(maxValue);
  const backgroundData = xAxisData.map(() => bgMax);

  const option = {
    grid: {
      left: showYaxis ? 40 : 20,
      right: 20,
      top: 40,
      bottom: rotateLabel ? 120 : 40,
    },

    tooltip: showTooltip
      ? {
          trigger: "item",
          formatter: function (params) {
            const value = params.value;
            if (!value) return "";

            const fullLabel = fullXAxisData[params.dataIndex];
            if (isTime) {
              return fullLabel + "<br/>" + formatTimeFromSeconds(value);
            } else if (unitLabel) {
              return fullLabel + "<br/>" + value + " " + unitLabel;
            } else {
              return fullLabel + "<br/>" + value;
            }
          },
        }
      : { show: false },

    xAxis: {
      type: "category",
      data: xAxisData,
      axisLine: {
        lineStyle: {
          type: "dashed",
          color: "#C5C5D3",
        },
      },
      axisLabel: {
        fontSize: 12,
        fontWeight: 400,
        color: "#606576",
        rotate: rotateLabel ? 45 : 0,
      },
      axisTick: {
        show: false,
      },
    },
    yAxis: {
      type: "value",
      max: bgMax,
      show: showYaxis,
      splitLine: {
        lineStyle: {
          type: "dashed",
          color: "#f4f4f4",
        },
      },
      axisTick: {
        show: false,
      },
    },
    series: [
      {
        type: "bar",
        data: backgroundData,
        barWidth: "40%",
        silent: true,
        barGap: "-100%",
        itemStyle: {
          color: "#f4f4f4",
        },
        z: 0,
      },
      {
        label: {
          show: showLabels,
          position: "top",
          fontSize: 12,
          fontWeight: 400,
          color: "#606576",
          formatter: function (params) {
            if (params.value == 0 || params.value === "0") {
              return "";
            }
            if (isTime) {
              return formatTimeFromSeconds(params.value);
            } else if (unitLabel) {
              return params.value + " " + unitLabel;
            } else {
              return params.value;
            }
          },
        },
        data: data,
        type: "bar",
        itemStyle: {
          color: function (params) {
            if (colors && colors.length > 0) {
              return colors[params.dataIndex] || colors[0];
            }
            return "#ff6a00";
          },
        },
        barWidth: "40%",
        z: 1,
      },
    ],
  };

  chartInstance.setOption(option);
  window.addEventListener("resize", () => chartInstance.resize());
}

function getSmartMax(value) {
  if (value <= 0) return 1;

  const magnitude = Math.pow(10, Math.floor(Math.log10(value))); // 1, 10, 100, 1000...
  const normalized = value / magnitude; // 1–10 range

  let rounded;
  if (normalized <= 1) rounded = 1;
  else if (normalized <= 2) rounded = 2;
  else if (normalized <= 5) rounded = 5;
  else rounded = 10;

  return rounded * magnitude;
}

// render pie chart
function renderPieChart({ elementId, data, labelSuffix, showValueName = null, concatValue = false }) {
  const chartElement = document.getElementById(elementId);
  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const option = {
    tooltip: {
      trigger: "item",
      formatter: function (params) {
        // dynamic field name from showValueName
        const displayValue = showValueName 
          ? params.data?.[showValueName] 
          : params.value;

        if (concatValue) {
          return `${params.name}<br/>${displayValue} ${concatValue}`;
        }

        return `${params.name}<br/>${displayValue}`;
      },
    },

    series: [
      {
        type: "pie",
        radius: "50%",
        data: data,

        label: {
          show: true,
          position: "outside",
          fontSize: 14,
          fontWeight: 500,
          color: "#1D1E25",
          formatter: function (params) {
            // if (labelSuffix) {
            //   return `${params.value}% ${labelSuffix}`;
            // }
            return params.name;
          },
        },
      },
      {
        type: "pie",
        radius: "50%",
        data: data,

        label: {
          show: true,
          position: "inside",
          fontSize: 14,
          color: "#fff",
          formatter: "{d}%",
        },
        labelLine: {
          show: false,
        },
        emphasis: {
          disabled: true,
        },
      },
    ],
  };

  chartInstance.setOption(option);
  window.addEventListener("resize", () => chartInstance.resize());
}

// half donut chart

function renderHalfDonutChart({ elementId, data = [], labelSuffix }) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) return;

  let chartInstance = echarts.getInstanceByDom(chartElement);
  if (chartInstance) {
    chartInstance.dispose();
  }

  chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const seriesData =
    data && data.length
      ? data
      : [
          {
            value: 1,
            name: "No data",
            itemStyle: { color: "#f3f4f6" },
          },
        ];

  const option = {
    tooltip: {
      trigger: "item",
      formatter: function (params) {
        const value = params.value;
        if (!value) return params.name;

        if (typeof formatTimeFromSeconds === "function") {
          return `${params.name}: ${formatTimeFromSeconds(value)}`;
        }

        if (labelSuffix) {
          return `${params.name}: ${value}${labelSuffix}`;
        }
        return `${params.name}: ${value}`;
      },
    },
    series: [
      {
        label: { show: false },
        name: "App Usage",
        type: "pie",
        radius: ["120%", "190%"],
        center: ["50%", "108%"],
        startAngle: 180,
        endAngle: 360,
        data: seriesData,
      },
      {
        type: "pie",
        radius: ["120%", "190%"],
        center: ["50%", "108%"],
        startAngle: 180,
        endAngle: 360,
        label: { show: false },
        data: seriesData,
        silent: true,
      },
    ],
  };

  chartInstance.setOption(option);
  window.addEventListener("resize", () => chartInstance.resize());
}

// render pie circle border chart
function renderPieCircleBorderChart({ elementId, data }) {
  const chartElement = document.getElementById(elementId);
  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const totalCount = data.reduce((sum, item) => sum + item.value, 0);

  const option = {
    tooltip: {
      trigger: "item",
    },
    legend: {
      orient: "horizontal",
      left: "center",
      bottom: "5%",
      textStyle: {
        color: "#606576",
      },
      itemWidth: 12,
      itemHeight: 12,
      itemGap: 12,
    },
    series: [
      {
        name: "Tickets",
        type: "pie",
        radius: ["32%", "40%"],
        avoidLabelOverlap: false,
        itemStyle: {
          borderRadius: 0,
          borderWidth: 0,
        },
        label: {
          show: false,
        },
        emphasis: {
          label: {
            show: false,
          },
        },
        labelLine: {
          show: false,
        },
        data: data,
      },
    ],
    graphic: [
      {
        type: "text",
        left: "center",
        top: "46%",
        style: {
          text: totalCount.toString(),
          fontSize: 20,
          fontWeight: 400,
          fill: "#606576",
        },
      },
      {
        type: "text",
        left: "center",
        top: "54%",
        style: {
          text: "Tickets",
          fontSize: 12,
          fill: "#606576",
        },
      },
    ],
  };

  chartInstance.setOption(option);
  window.addEventListener("resize", () => chartInstance.resize());
}

// render multi bar chart
function renderMultiBarChart({ elementId, data, colors, xAxisData }) {
  const chartElement = document.getElementById(elementId);
  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const series = data.map((seriesItem, index) => ({
    type: "bar",
    data: seriesItem.values,
    barWidth: "15%",

    itemStyle: {
      color: colors[index],
    },
    emphasis: {
      focus: "series",
    },
  }));

  const option = {
    grid: {
      left: 40,
      right: 20,
      top: 40,
      bottom: 40,
    },
    label: {
      show: true,
      position: "top",
      fontSize: 10,
      fontWeight: 400,
      color: "#1D1E25",
      formatter: function (params) {
        return params.value;
      },
    },
    tooltip: {
      trigger: "axis",
      axisPointer: {
        type: "shadow",
      },
    },
    legend: {
      data: data.map((item) => item.name),
    },

    xAxis: [
      {
        type: "category",
        axisTick: {
          show: false,
        },
        data: xAxisData,

        axisLine: {
          lineStyle: {
            type: "dashed",
            color: "#C5C5D3",
          },
        },
        axisLabel: {
          fontSize: 12,
          fontWeight: 400,
          color: "#606576",
        },
      },
    ],
    yAxis: [
      {
        type: "value",
        splitLine: {
          lineStyle: {
            type: "dashed",
            color: "#C5C5D3",
          },
        },
        axisTick: {
          show: false,
        },
      },
    ],
    series: series,
  };

  chartInstance.setOption(option);
}

// dot line chart
function renderDotLineChart({
  elementId,
  data,
  colors,
  xAxisData,
  unitLabel,
  showLabels = false,
  showTooltip = true,
}) {
  const chartElement = document.getElementById(elementId);
  const chartInstance = echarts.init(chartElement, null, {
    renderer: "svg",
    useDirtyRect: false,
  });

  const option = {
    grid: {
      left: 40,
      right: 20,
      top: 40,
      bottom: 40,
    },

    tooltip: showTooltip
      ? {
          trigger: "axis",
          axisPointer: {
            type: "line",
          },
          textStyle: {
            color: "#1D1E25",
            fontSize: 12,
            fontWeight: 400,
          },
          formatter: function (params) {
            const unit = unitLabel || "mins";
            return params[0].name + "<br/>" + params[0].value + " " + unit;
          },
        }
      : { show: false },

    legend: {
      show: false,
    },

    xAxis: {
      type: "category",
      data: xAxisData,
      axisLine: {
        lineStyle: {
          color: "#C5C5D3",
          type: "dashed",
        },
      },
      axisLabel: {
        color: "#606576",
        fontSize: 12,
        fontWeight: 400,
      },
      axisTick: {
        show: false,
      },
    },

    yAxis: {
      type: "value",
      splitLine: {
        lineStyle: {
          type: "dashed",
          color: "#C5C5D3",
        },
      },
      axisLine: {
        lineStyle: {
          color: "#C5C5D3",
          type: "dashed",
        },
      },
      axisLabel: {
        color: "#606576",
      },
    },

    series: [
      {
        name: "Line",
        type: "line",
        data: data,
        smooth: true,
        symbol: "circle",
        symbolSize: 10,
        showSymbol: true,
        lineStyle: {
          color: colors[0] || "#14c8d4",
          width: 2,
        },
        itemStyle: {
          color: colors[0] || "#14c8d4",
          borderColor: "#fff",
          borderWidth: 1,
        },

        label: {
          show: showLabels,
          position: "outside",
          fontSize: 10,
          fontWeight: 400,
          color: "#1D1E25",
          formatter: function (params) {
            return params.value;
          },
        },
      },
    ],
  };

  chartInstance.setOption(option);
}

function formatTimeFromSeconds(totalSeconds) {
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  return `${hours}h ${minutes}m ${seconds}s`;
}

// ========================== Chart Js End ==============
