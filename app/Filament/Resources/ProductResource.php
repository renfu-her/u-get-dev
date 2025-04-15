<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = '商店管理';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return '產品管理';
    }

    public static function getModelLabel(): string
    {
        return '產品';
    }

    public static function getPluralModelLabel(): string
    {
        return '產品';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('store_id')
                    ->relationship('store', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('商店'),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('分類'),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('名稱')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->label('網址')
                    ->unique(ignoreRecord: true),

                Forms\Components\RichEditor::make('description')
                    ->label('描述')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('NT$')
                    ->label('價格'),

                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('庫存'),

                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('products')
                    ->label('主要圖片'),

                Forms\Components\FileUpload::make('images')
                    ->multiple()
                    ->image()
                    ->directory('products')
                    ->label('其他圖片'),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->label('啟用')
                    ->default(true),

                Forms\Components\Toggle::make('is_featured')
                    ->required()
                    ->label('精選')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name')
                    ->sortable()
                    ->searchable()
                    ->label('商店'),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->label('分類'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('名稱'),

                Tables\Columns\TextColumn::make('price')
                    ->money('TWD')
                    ->sortable()
                    ->label('價格'),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->label('庫存'),

                Tables\Columns\ImageColumn::make('image')
                    ->label('圖片'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('啟用'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('精選'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->label('建立時間')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store')
                    ->relationship('store', 'name')
                    ->label('商店'),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('分類'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('啟用狀態'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('精選狀態'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
