<?php

namespace app\controllers;

use Yii;
use app\models\Monster;
use app\models\MonsterSearch;
use yii\base\Theme;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;


/**
 * MonsterController implements the CRUD actions for Monster model.
 */
class MonsterController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['member']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['admin']
                    ]
                ],
                'denyCallback' => function($rule, $action) {
                    if ($action->id == 'delete') {
                        throw new ForbiddenHttpException('Only administrators can delete users.');
                    } else {
                        if (Yii::$app->user->isGuest) {
                            Yii::$app->user->loginRequired();
                        }
                    }
                }
            ]
        ];
    }

    /**
     * Lists all Monster models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MonsterSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Monster model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Monster model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Monster();
        $model->hashPassword = true;
        $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->mailer->compose('register',['model'=>$model])
                ->setFrom('admin@monstermash.dev')
                ->setTo('test@test.com')
                ->setSubject('Welcome to Monstermash!')
                ->send();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Monster model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        if ($model->load(Yii::$app->request->post())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing Monster model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Monster model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Monster the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Monster::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $user = Yii::$app->user;

        if (!$user->isGuest) {
            if ($user->identity->gender == 'f') {
                Yii::$app->view->theme = new Theme([
                    'pathMap' => [
                        '@app/views' => [
                            '@app/themes/feminine'
                        ]
                    ]
                ]);

                Yii::$app->assetManager->getBundle('app\assets\AppAsset')->css = ['css/feminine.css'];

            }
        }

        return true;
    }
}
