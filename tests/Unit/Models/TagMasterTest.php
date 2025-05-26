<?php

namespace Tests\Unit\Models;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TagMaster;
use App\Models\User;
use Tests\TestCase;
use Tests\TestHelpers;

class TagMasterTest extends TestCase
{
    use TestHelpers;

    protected $tagMaster;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->tagMaster = $this->createTagMaster();
    }

    /**
     * @test
     */
    public function TagMasterモデルが正常に作成できること()
    {
        $this->assertInstanceOf(TagMaster::class, $this->tagMaster);
        $this->assertNotNull($this->tagMaster->id);
        $this->assertNotNull($this->tagMaster->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルのfillable属性が正しく設定されていること()
    {
        $fillable = $this->tagMaster->getFillable();
        
        $expectedFillable = [
            'name', 
            'created_at',
            'updated_at',
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    /**
     * @test
     */
    public function TagMasterモデルでmass_assignmentが正常に動作すること()
    {
        $data = [
            'name' => 'マスアサインメントテストタグ',
        ];
        
        $tagMaster = TagMaster::create($data);
        
        $this->assertInstanceOf(TagMaster::class, $tagMaster);
        $this->assertEquals($data['name'], $tagMaster->name);
    }

    /**
     * @test
     */
    public function questionsリレーションでQuestionモデルと正しく関連付けられていること()
    {
        $relation = $this->tagMaster->questions();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relation);
        $this->assertEquals('questions', $relation->getTable());
    }

    /**
     * @test
     */
    public function answersリレーションでAnswerモデルと正しく関連付けられていること()
    {
        $relation = $this->tagMaster->answers();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $relation);
        $this->assertEquals('answers', $relation->getTable());
    }

    /**
     * @test
     */
    public function TagMasterモデルでタイムスタンプが自動的に設定されること()
    {
        $newTagMaster = TagMaster::create([
            'name' => 'タイムスタンプテストタグ',
        ]);
        
        $this->assertNotNull($newTagMaster->created_at);
        $this->assertNotNull($newTagMaster->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newTagMaster->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $newTagMaster->updated_at);
    }

    /**
     * @test
     */
    public function TagMasterモデルの更新でupdated_atが変更されること()
    {
        $originalUpdatedAt = $this->tagMaster->updated_at;
        
        // 少し待ってから更新
        sleep(1);
        $this->tagMaster->update(['name' => '更新されたタグ名']);
        
        $this->assertNotEquals($originalUpdatedAt, $this->tagMaster->updated_at);
        $this->assertEquals('更新されたタグ名', $this->tagMaster->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルの削除が正常に動作すること()
    {
        $tagMasterId = $this->tagMaster->id;
        
        $this->tagMaster->delete();
        
        $this->assertNull(TagMaster::find($tagMasterId));
    }

    /**
     * @test
     */
    public function TagMasterモデルで空文字のnameでも作成できること()
    {
        $tagMaster = TagMaster::create(['name' => '']);
        
        $this->assertInstanceOf(TagMaster::class, $tagMaster);
        $this->assertEquals('', $tagMaster->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルで長いnameでも作成できること()
    {
        // MySQLのstring型のデフォルトは255文字まで
        $longName = str_repeat('あ', 85); // 日本語は3バイトなので85文字まで
        $tagMaster = TagMaster::create(['name' => $longName]);
        
        $this->assertInstanceOf(TagMaster::class, $tagMaster);
        $this->assertEquals($longName, $tagMaster->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルで同じ名前のタグを複数作成できること()
    {
        $tagName = '重複テストタグ';
        
        $tag1 = TagMaster::create(['name' => $tagName]);
        $tag2 = TagMaster::create(['name' => $tagName]);
        
        $this->assertEquals($tagName, $tag1->name);
        $this->assertEquals($tagName, $tag2->name);
        $this->assertNotEquals($tag1->id, $tag2->id);
    }

    /**
     * @test
     */
    public function TagMasterモデルで日本語の名前が正しく保存されること()
    {
        $japaneseTagName = 'プログラミング・技術・Web開発';
        $tagMaster = TagMaster::create(['name' => $japaneseTagName]);
        
        $this->assertEquals($japaneseTagName, $tagMaster->name);
        
        // データベースから再取得して確認
        $retrievedTag = TagMaster::find($tagMaster->id);
        $this->assertEquals($japaneseTagName, $retrievedTag->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルで特殊文字を含む名前が正しく保存されること()
    {
        $specialCharTagName = 'C++/C#.NET & JavaScript (ES6+)';
        $tagMaster = TagMaster::create(['name' => $specialCharTagName]);
        
        $this->assertEquals($specialCharTagName, $tagMaster->name);
        
        // データベースから再取得して確認
        $retrievedTag = TagMaster::find($tagMaster->id);
        $this->assertEquals($specialCharTagName, $retrievedTag->name);
    }

    /**
     * @test
     */
    public function TagMasterモデルでcreated_atとupdated_atが適切に設定されること()
    {
        $tagMaster = TagMaster::create(['name' => 'タイムスタンプ検証タグ']);
        
        $this->assertNotNull($tagMaster->created_at);
        $this->assertNotNull($tagMaster->updated_at);
        
        // 作成時はcreated_atとupdated_atが同じ値であること
        $this->assertEquals(
            $tagMaster->created_at->format('Y-m-d H:i:s'),
            $tagMaster->updated_at->format('Y-m-d H:i:s')
        );
    }

    /**
     * @test
     */
    public function TagMasterモデルでtoArray変換が正常に動作すること()
    {
        $tagArray = $this->tagMaster->toArray();
        
        $this->assertIsArray($tagArray);
        $this->assertArrayHasKey('id', $tagArray);
        $this->assertArrayHasKey('name', $tagArray);
        $this->assertArrayHasKey('created_at', $tagArray);
        $this->assertArrayHasKey('updated_at', $tagArray);
    }

    /**
     * @test
     */
    public function TagMasterモデルでtoJson変換が正常に動作すること()
    {
        $tagJson = $this->tagMaster->toJson();
        
        $this->assertIsString($tagJson);
        
        $decodedJson = json_decode($tagJson, true);
        $this->assertIsArray($decodedJson);
        $this->assertArrayHasKey('id', $decodedJson);
        $this->assertArrayHasKey('name', $decodedJson);
    }
}