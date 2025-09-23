function drawSpiderChart(containerSelector, options) {
  console.log("options", options);
  const data = options.data;
  const singleColor = options.color;
  const lineColor = options.lineColor || "white";
  const gradientColors = options.gradient;

  const margin = { top: 100, right: 100, bottom: 100, left: 100 };
  const width =
    Math.min(700, window.innerWidth - 10) - margin.left - margin.right;
  const height = Math.min(
    width,
    window.innerHeight - margin.top - margin.bottom - 20
  );

  const cfg = {
    w: width,
    h: height,
    margin: margin,
    maxValue: 1,
    levels: 5,
    opacityArea: 0.35,
  };

  const allAxis = data[0].map((i) => i.axis);
  const total = allAxis.length;
  const radius = Math.min(cfg.w / 2, cfg.h / 2);
  const angleSlice = (Math.PI * 2) / total;
  const rScale = d3.scaleLinear().range([0, radius]).domain([0, cfg.maxValue]);

  d3.select(containerSelector).select("svg").remove();
  const svg = d3
    .select(containerSelector)
    .append("svg")
    .attr("width", cfg.w + cfg.margin.left + cfg.margin.right)
    .attr("height", cfg.h + cfg.margin.top + cfg.margin.bottom)
    .append("g")
    .attr(
      "transform",
      `translate(${cfg.w / 2 + cfg.margin.left}, ${cfg.h / 2 + cfg.margin.top})`
    );

  const defaultColorScale = d3.scaleOrdinal(d3.schemeCategory10);

  if (gradientColors && gradientColors.length === 2) {
    const gradientId =
      "spider-gradient-" + d3.select(containerSelector).attr("id");
    const defs = svg.append("defs");
    const linearGradient = defs
      .append("linearGradient")
      .attr("id", gradientId)
      .attr("x1", "0%")
      .attr("y1", "0%")
      .attr("x2", "100%")
      .attr("y2", "100%"); // Diagonal

    linearGradient
      .append("stop")
      .attr("class", "start")
      .attr("offset", "0%")
      .attr("stop-opacity", 1)
      .attr("stop-color", gradientColors[0]);

    linearGradient
      .append("stop")
      .attr("class", "end")
      .attr("offset", "100%")
      .attr("stop-opacity", 1)
      .attr("stop-color", gradientColors[1]);
  }

  const grid = svg.append("g").attr("class", "grid");

  grid
    .selectAll(".levels")
    .data(d3.range(1, cfg.levels + 1).reverse())
    .enter()
    .append("circle")
    .attr("class", "gridCircle")
    .attr("r", (d) => (radius / cfg.levels) * d)
    .style("fill", "#CDCDCD")
    .style("stroke", "#CDCDCD")
    .style("fill-opacity", 0.1);

  const axis = grid
    .selectAll(".axis")
    .data(allAxis)
    .enter()
    .append("g")
    .attr("class", "axis");

  axis
    .append("line")
    .attr("x1", 0)
    .attr("y1", 0)
    .attr("x2", (d, i) => radius * Math.cos(angleSlice * i - Math.PI / 2))
    .attr("y2", (d, i) => radius * Math.sin(angleSlice * i - Math.PI / 2))
    .attr("class", "line")
    .style("stroke", lineColor)
    .style("stroke-width", "2px");

  axis
    .append("text")
    .attr("class", "legend")
    .style("font-size", "11px")
    .attr("text-anchor", "middle")
    .attr("dy", "0.35em")
    .attr("x", (d, i) => radius * 1.1 * Math.cos(angleSlice * i - Math.PI / 2))
    .attr("y", (d, i) => radius * 1.1 * Math.sin(angleSlice * i - Math.PI / 2))
    .text((d) => d);

  const radarLine = d3
    .lineRadial()
    .curve(d3.curveLinearClosed)
    .radius((d) => rScale(d.value))
    .angle((d, i) => i * angleSlice);

  const blobWrapper = svg
    .selectAll(".radarWrapper")
    .data(data)
    .enter()
    .append("g")
    .attr("class", "radarWrapper");

  blobWrapper
    .append("path")
    .attr("class", "radarArea")
    .attr("d", (d) => radarLine(d))
    .style("fill", (d, i) => {
      if (gradientColors) {
        return `url(#spider-gradient-${d3
          .select(containerSelector)
          .attr("id")})`;
      }
      if (singleColor) {
        return singleColor;
      }
      return defaultColorScale(i);
    })
    .style("fill-opacity", cfg.opacityArea);

  blobWrapper
    .append("path")
    .attr("class", "radarStroke")
    .attr("d", (d) => radarLine(d))
    .style("stroke-width", "2px")
    .style("stroke", (d, i) => {
      if (gradientColors) return gradientColors[0];

      if (singleColor) return singleColor;
      return defaultColorScale(i);
    })
    .style("fill", "none");
}
