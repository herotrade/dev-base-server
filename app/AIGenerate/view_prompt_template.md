# AI 页面开发提示词模板

## 基本指令

我需要创建一个新的页面功能，请根据以下配置和标准模板为我实现完整的代码。请严格遵循模板的编码风格和最佳实践。

## 参考资料

请首先阅读以下资料，以确保你理解开发规范和要求：

1. 标准模板文档(`standard_template.md`)
2. 配置文件模板(`config_template.json`)
3. 开发指南(`development_guide.md`)

## 配置信息

以下是我需要开发的页面配置信息：

```json
{
  "module": "模块名称", // 示例：strategy，表示项目中的模块名称
  "feature": {
    "name": "功能名称", // 示例：currency，表示功能的英文名称，用于生成文件路径
    "path": "功能路径", // 示例：/currency，表示功能的前端路由路径
    "description": "功能描述", // 示例：交易对管理，表示功能的中文描述
    "permissions": {
      "create": "创建权限标识", // 示例：strategy:currency:create，表示创建操作的权限标识
      "read": "查看权限标识", // 示例：strategy:currency:read，表示查看操作的权限标识
      "update": "更新权限标识", // 示例：strategy:currency:update，表示更新操作的权限标识
      "delete": "删除权限标识" // 示例：strategy:currency:delete，表示删除操作的权限标识
    },
    "i18n": {
      "enabled": true, // 是否启用国际化，true表示启用，false表示不启用
      "default_language": "zh_CN", // 默认语言，一般为简体中文
      "supported_languages": ["zh_CN", "en", "zh_Hant", "ja", "ko"] // 支持的语言列表，根据项目需求配置
    }
  },
  "entity": {
    "name": "实体名称", // 示例：Currency，表示实体的名称，首字母大写，用于生成接口类型等
    "fields": [
      // ID字段一般为系统自动生成，不需要在表单中填写
      {
        "name": "id", // 字段名称，表示数据库中的字段名
        "label": "ID", // 字段标签，表示前端展示的字段名称
        "type": "number", // 字段类型，表示数据类型
        "isRequired": false, // 是否必填，false表示非必填
        "isSearchable": false, // 是否可搜索，false表示不可搜索
        "isTableColumn": true, // 是否为表格列，true表示在表格中显示
        "isSortable": true // 是否可排序，true表示可以按此字段排序
      },
      // 业务字段示例
      {
        "name": "名称字段", // 示例：name，表示数据库中的字段名
        "label": "名称标签", // 示例：名称，表示前端展示的字段名称
        "type": "string|number|date|boolean|array|object", // 字段类型，根据实际数据选择合适的类型
        "childrenForm": false, // 是否使用子表单，为true时配合type:array实现子表单编辑
        "defaultValue": "", // 默认值，新建时的默认值
        "isRequired": true, // 是否必填，true表示必填
        "isSearchable": true, // 是否可搜索，true表示可搜索
        "isTableColumn": true, // 是否为表格列，true表示在表格中显示
        "isSortable": true, // 是否可排序，true表示可以按此字段排序
        "formConfig": {
          // 表单配置，定义在表单中如何渲染此字段
          "component": "input|select|datePicker|inputNumber|switch|radio|checkbox|upload", // 表单组件类型，根据字段类型选择
          "rules": [
            // 表单验证规则
            {
              "required": true, // 是否必填
              "message": "验证错误信息" // 验证失败时的提示信息，示例：请输入名称
            }
          ],
          "props": {
            // 组件属性
            "placeholder": "请输入或选择", // 占位文本d
            "clearable": true // 是否可清除
          },
          "options": [
            // select、radio、checkbox等组件的选项列表
            { "label": "选项1", "value": "值1" } // 选项配置，label为显示文本，value为实际值
          ]
        },
        "tableConfig": {
          // 表格配置，定义在表格中如何渲染此字段
          "width": "100px", // 列宽度
          "align": "left|center|right", // 对齐方式
          "formatter": "文本|标签|图片|链接", // 格式化方式，根据需要选择合适的展示方式
          "formatterOptions": {} // 格式化选项，根据formatter类型填写对应的配置
        },
        "searchConfig": {
          // 搜索配置，定义在搜索区域如何渲染此字段
          "component": "input|select|datePicker", // 搜索组件类型，根据字段类型选择
          "props": {
            // 组件属性
            "placeholder": "请输入搜索内容", // 占位文本
            "clearable": true // 是否可清除
          }
        }
      }
    ]
  },
  "api": {
    "base": "/admin/接口路径", // 示例：/admin/currency，表示API的基础路径
    "methods": {
      // API方法配置
      "list": "GET /list", // 列表查询接口
      "info": "GET /:id", // 详情查询接口
      "create": "POST /", // 创建接口
      "update": "PUT /:id", // 更新接口
      "delete": "DELETE /batch/delete" // 删除接口
    }
  },
  "ui": {
    "table": {
      // 表格UI配置
      "pageSize": 15, // 每页显示条数
      "columns": [
        // 表格显示的列，按顺序排列
        "id",
        "名称字段",
        "created_at"
      ],
      "sortFields": [
        // 可排序的字段列表
        { "field": "id", "label": "ID" },
        { "field": "created_at", "label": "创建时间" }
      ]
    },
    "search": {
      // 搜索UI配置
      "items": [
        // 搜索项，按顺序排列
        "名称字段",
        "CreatedAtOfDay", // 按日期查询，支持选择某一天
        "CreatedAtBetween" // 按日期范围查询，支持选择开始和结束日期
      ]
    },
    "form": {
      // 表单UI配置
      "labelWidth": "80px", // 表单标签宽度
      "cols": { "md": 12, "xs": 24 }, // 表单布局，可以设置不同屏幕尺寸下的列宽
      "viewType": "drawer" // 默认：drawer  drawer：抽屉  dialog：弹框
    }
  }
}
```

## 任务要求

1. 请基于上面的配置和标准模板，生成以下文件：

   - API 接口文件 (`api/[功能名].ts`)
   - 列表页面 (`views/[功能名]/index.vue`)
   - 表单页面 (`views/[功能名]/form.vue`)
   - 搜索项配置 (`views/[功能名]/data/getSearchItems.tsx`)
   - 表格列配置 (`views/[功能名]/data/getTableColumns.tsx`)
   - 表单项配置 (`views/[功能名]/data/getFormItems.tsx`)

2. 国际化要求：

   - 如果配置中`i18n.enabled`为`true`，请确保生成的代码完全支持国际化
   - 所有文本标签使用`t('xxx')`函数包装
   - 如果`i18n.enabled`为`false`，请不要包含任何国际化相关代码

3. 代码规范要求：

   - 严格遵循标准模板中的编码风格和结构
   - 保持代码整洁、可读性强
   - 适当添加注释，特别是关键逻辑
   - 确保类型定义正确
   - 当有字段配置 type:array 且存在 childrenForm:true 时，表示这个字段是一个需要通过子表单进行表单渲染的字段，需要使用`MaChildrenForm`组件处理，在标准模板中以支持设置多语言的字段作为案例，在实际业务中不仅限为多语言类型字段需要根据实际需求配置合适的子表单

4. 功能要求：
   - 实现列表页面的搜索、分页、排序功能
   - 实现新增、编辑、删除功能
   - 实现表单验证
   - 按照字段配置生成对应的表单项和表格列

## 期望输出

请按顺序输出以下内容：

1. API 接口文件 (`api/[功能名].ts`)
2. 列表页面 (`views/[功能名]/index.vue`)
3. 表单页面 (`views/[功能名]/form.vue`)
4. 搜索项配置 (`views/[功能名]/data/getSearchItems.tsx`)
5. 表格列配置 (`views/[功能名]/data/getTableColumns.tsx`)
6. 表单项配置 (`views/[功能名]/data/getFormItems.tsx`)

## 补充说明

如有任何不明确的地方，请遵循以下原则：

1. 参考标准模板中的编码风格和最佳实践
2. 根据已有配置推断合理的实现方式
3. 如有疑问，可以向我提问确认
