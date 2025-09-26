from graphviz import Digraph

# Crear un nuevo diagrama de flujo
dot = Digraph(format='png')
dot.attr(rankdir='TB', size='10')

# Definir los nodos del proceso actual
dot.node('A', 'Cliente solicita software de salud', shape='parallelogram', style='filled', fillcolor='lightblue')
dot.node('B', 'Evaluación interna: ¿Existe software en el portafolio?', shape='diamond', style='filled', fillcolor='lightgray')
dot.node('C', 'No hay software disponible', shape='box', style='filled', fillcolor='red')
dot.node('D', 'Se ofrece desarrollo desde cero', shape='box', style='filled', fillcolor='orange')
dot.node('E', 'Cliente evalúa la propuesta', shape='parallelogram', style='filled', fillcolor='lightblue')
dot.node('F', 'Cliente acepta: Se inicia desarrollo', shape='box', style='filled', fillcolor='green')
dot.node('G', 'Cliente rechaza: Busca otra empresa', shape='box', style='filled', fillcolor='red')
dot.node('H', 'Pérdida de oportunidad de venta', shape='box', style='filled', fillcolor='red')

# Definir las conexiones entre los nodos
dot.edge('A', 'B')
dot.edge('B', 'C', label='No')
dot.edge('C', 'D')
dot.edge('D', 'E')
dot.edge('E', 'F', label='Sí')
dot.edge('E', 'G', label='No')
dot.edge('G', 'H')

# Renderizar y guardar la imagen
diagram_path = "/mnt/data/diagrama_operacion_actual"
dot.render(diagram_path)

# Devolver la ruta del archivo generado
diagram_path + ".png"