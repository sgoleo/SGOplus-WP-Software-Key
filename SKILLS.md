# 遷移工程技能手冊 (SKILLS.md)

## 技能 1：資料庫工程 (Database Engineering)
**目標**：資料庫初始化與正規化。
**執行內容**：
- 撰寫建立新的授權金鑰與網域關聯表結構的 SQL 邏輯。
- 欄位設計需能完整承接舊版 Software License Manager 的歷史資料。
- 確保符合現代化資料庫設計原則。

## 技能 2：背景遷移 (Background Migration)
**目標**：實作非同步遷移引擎。
**執行內容**：
- 實作背景處理任務，分批次將舊資料轉換至新資料表。
- 確保遷移過程不影響系統正常運作。

## 技能 3：API 開發 (API Development)
**目標**：建構現代化 REST API 端點。
**執行內容**：
- 建立具備 Nonce 防護與能力校驗（Capability checks）的 API。
- 支援授權驗證、啟用與停用等功能。

## 技能 4：前端工程 (Frontend Engineering)
**目標**：建構 SPA 後台介面。
**執行內容**：
- 利用 Vite、React、Tailwind CSS 與 @wordpress/dataviews 建構管理介面。
- 整合 DataViews API 提供流暢的資料管理體驗。
