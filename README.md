## アプリ名
本人確認君
## アプリケーション概要 
ドコモショップでの手続きに必要な書類を確認することのできるアプリです。
## URL
https://syoruikakuninn-36372.herokuapp.com/
## テストアカウント
email: test@test, password: 0000000a
## 制作意図
必要な書類が足りずに手続きができないという人が少しでも減ることを考えてこのアプリを作成しました。
ドコモショップは、手続きの内容によって様々な書類が必要になります。
しかし、来店前に必要な書類を確認する方法は電話問い合わせしかないため、必要な書類を気軽に確認することも難しく、結果として書類不足で手続きできないという状況が多々あります。
このアプリは、そのような状況を解決するべく、スマートフォンから手軽に必要書類を確認できるようにするために開発しました。
## DEMO
#### 会員情報の確認
[![Image from Gyazo](https://i.gyazo.com/7d29e8e4963d4dbb6188d963f561e2ca.gif)](https://gyazo.com/7d29e8e4963d4dbb6188d963f561e2ca)
#### 身分証明書の登録
[![Image from Gyazo](https://i.gyazo.com/b69c2a12073584d5d1f8d95d263f4c26.gif)](https://gyazo.com/b69c2a12073584d5d1f8d95d263f4c26)
#### 手続きに必要な書類の確認
[![Image from Gyazo](https://i.gyazo.com/87b1c594b23242c8cc94890de20efe8b.gif)](https://gyazo.com/87b1c594b23242c8cc94890de20efe8b)
## 工夫したポイント
スマートフォンのような、画面の小さなデバイスでも使いやすいようにデザインに拘りました。
webアプリケーションでは、まずPCからのアクセスを第一に想定しているものも多いと思います。
しかし、スマートフォンから手軽に事前確認を、と謳う携帯キャリア用のサービスである以上、スマートフォンからの見やすさに拘りました。
具体的にはドロップダウンやレスポンシブデザインなどを用いて、画面が小さくても見づらくならないようなレイアウトを追求しました。
## 使用技術
フロントエンド：　HTML, CSS, JavaScript, jQuery, BootStrap
サーバーサイド: PHP, Laravel
## 今後実装したい機能
法人名義のお客様や、契約者以外の代理人が来店する場合の必要書類も確認できるようにしたいと考えています。
現在、このサービスで表示される必要書類は、成人した契約者が自分で来店する場合を想定しています。
しかし、手続きに必要な書類は、契約者本人が来店するのかどうか、また、契約者が個人名義か法人名義かなどによって変わってきます。
法人名義の場合や契約者本人の来店がない場合の必要書類についても確認できるようになれば、お客様・携帯ショップ双方にとってより便利なサービスになるので、法人名義や代理人来店時に対応できる機能を実装したいと考えています。

## DB設計

### usersテーブル

| Column         | Type    | Options                   |
| -------------- | ------- | ------------------------- |
| name           | string  | null: false               |
| email          | string  | null: false, unique: true |
| password       | string  | null: false               | 
| birthday       | date    | null: false               | 
| is_corporation | boolean | default: null             |

#### Associations

- hasMany(Memo)
- hasMany(UserLicense)
- hasMany(UserPay)
- hasMany(UserPaper)

c
### memosテーブル

| Column            | Type    | Options                    |
| ----------------- | ------- | -------------------------- |
| user_id           | integer | null: false, foreign: true |
| procedure_id      | integer | null: false                |
| notice            | text    |                            |

#### Associations

- belongsTo(User)
- hasMany(MemoLicense)
- hasMany(MemoPay)
- hasMany(MemoPaper)


### memo_licensesテーブル

| Column            | Type    | Options                    |
| ----------------- | ------- | -------------------------- |
| memo_id           | integer | null: false, foreign: true |
| license_id        | integer | null: false                |

#### Associations

- belongsTo(Memo)


### meo_paysテーブル

| Column            | Type    | Options                    |
| ----------------- | ------- | -------------------------- |
| memo_id           | integer | null: false, foreign: true |
| pay_id            | integer | null: false                |

#### Associations

- belongsTo(Memo)


### memo_papersテーブル

| Column            | Type    | Options                    |
| ----------------- | ------- | -------------------------- |
| memo_id           | integer | null: false, foreign: true |
| paper_id          | integer | null: false                |

#### Associations

- belongsTo(Memo)


## ER図
[![Image from Gyazo](https://i.gyazo.com/5fa1a7932bc5736373a2249ab200997d.png)](https://gyazo.com/5fa1a7932bc5736373a2249ab200997d)
